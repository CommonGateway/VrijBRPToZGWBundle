<?php

namespace CommonGateway\VrijBRPToZGWBundle\Service;

use App\Entity\ObjectEntity;
use CommonGateway\CoreBundle\Service\CacheService;
use CommonGateway\CoreBundle\Service\GatewayResourceService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Service for project specific code.
 *
 * Service for project specific code. More specifically: creating notifications and setting defaults.
 *
 * @author  Robert Zondervan, Barry Brands, Ruben van der Linde
 * @license EUPL<github.com/ConductionNL/contactcatalogus/blob/master/LICENSE.md>
 *
 * @category Service
 */
class VrijBrpService
{


    /**
     * The constructor
     *
     * @param CacheService              $cacheService    The cacheservice
     * @param GatewayResourceService    $resourceService The resourceservice
     * @param EntityManagerInterface    $entityManager   The Entity manager
     * @param NewSynchronizationService $syncService     The synchronizationservice
     */
    public function __construct(
        private readonly CacheService $cacheService,
        private readonly GatewayResourceService $resourceService,
        private readonly EntityManagerInterface $entityManager,
        private readonly NewSynchronizationService $syncService,
    ) {

    }//end __construct()


    /**
     * Set defaults for configuration of synchronize action.
     *
     * @param array $configuration Incoming configuration.
     *
     * @return array The updated configuration array.
     */
    public function setVrijBRPDefaults(array $configuration): array
    {
        if (isset($configuration['endpoint']) === false) {
            $configuration['endpoint'] = '/v1/api/dossiers/search';
        }

        if (isset($configuration['method']) === false) {
            $configuration['method'] = 'POST';
        }

        if (isset($configuration['body']) === false) {
            $configuration['body'] = [
                'types' => [
                    'intra_mun_relocation',
                    'inter_mun_relocation',
                ],
            ];
        }

        return $configuration;

    }//end setVrijBRPDefaults()


    /**
     * Creates a status notification.
     *
     * @param array $data   incoming data.
     * @param array $config incoming configuration.
     *
     * @return array The updated data array.
     */
    public function createStatusNotification(array $data, array $config): array
    {
        $object = $data['object'];

        if ($object instanceof ObjectEntity) {
            $objectData = $object->toArray(['embedded' => true, 'user' => $this->cacheService->getObjectUser(objectEntity: $object)]);

            $now           = new DateTime();
            $message       = [
                'kanaal'       => 'zaken',
                'hoofdObject'  => $object->getValue('zaak')->getUri(),
                'resource'     => 'status',
                'resourceUrl'  => $object->getUri(),
                'actie'        => 'create',
                'aanmaakdatum' => $now->format('c'),
            ];
            $schema        = $this->resourceService->getSchema(
                reference: 'https://zgw.opencatalogi.nl/schema/nrc.message.schema.json',
                pluginName: 'common-gateway/vrijbrp-to-zgw-bundle'
            );
            $messageObject = new ObjectEntity(entity: $schema);
            $messageObject->hydrate($message);
            $this->entityManager->persist($messageObject);
        }//end if

        return $data;

    }//end createStatusNotification()


    /**
     * Specific extension: fetch second source and run mapping.
     *
     * @param ObjectEntity $object Incoming object
     * @param array        $array  Serialised incoming object
     *
     * @return ObjectEntity The updated objectEntity.
     */
    public function extendSync(ObjectEntity $object, array $array): ObjectEntity
    {
        $type = $array['toelichting'];

        $detailMapping = $this->resourceService->getMapping('https://commongateway.nl/mapping/vrijbrp.dossierToZaakDetail.mapping.json', 'common-gateway/vrijbrp-to-zgw-bundle');

        if ($type === 'intra_mun_relocation') {
            $endpoint = '/api/v1/relocations/intra';
        } else {
            $endpoint = '/api/v1/relocations/inter';
        }

        $synchronization = $object->getSynchronizations()[0];

        $synchronization->setEndpoint($endpoint);
        $synchronization->setMapping($detailMapping);

        $this->syncService->synchronizeFromSource($synchronization);

        return $synchronization->getObject();

    }//end extendSync()


    /**
     * Fire notification for case creation. Also extend case with data from detail endpoint.
     *
     * @param array $data   Incoming data
     * @param array $config Incoming configuration
     *
     * @return array The updated data array.
     */
    public function createCaseNotification(array $data, array $config): array
    {
        $object = $data['object'];

        if ($object instanceof ObjectEntity === true) {
//            $array = $object->toArray(['embedded' => true]);
//
//            if (in_array(needle: $array['toelichting'], haystack: ['intra_mun_relocation', 'inter_mun_relocation']) === true) {
//                $object = $this->extendSync($object, $array);
//            }

            $now           = new DateTime();
            $message       = [
                'kanaal'       => 'zaken',
                'hoofdObject'  => $object->getValue('zaak')->getUri(),
                'resource'     => 'rol',
                'resourceUrl'  => $object->getUri(),
                'actie'        => 'create',
                'aanmaakdatum' => $now->format('c'),
            ];

            $schema        = $this->resourceService->getSchema(
                reference: 'https://zgw.opencatalogi.nl/schema/nrc.message.schema.json',
                pluginName: 'common-gateway/vrijbrp-to-zgw-bundle'
            );
            $messageObject = new ObjectEntity(entity: $schema);
            $messageObject->hydrate($message);
            $this->entityManager->persist($messageObject);
        }//end if

        return $data;

    }//end createCaseNotification()


}//end class
