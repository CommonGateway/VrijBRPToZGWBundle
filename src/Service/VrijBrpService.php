<?php

namespace CommonGateway\VrijBRPToZGWBundle\Service;

use App\Entity\ObjectEntity;
use CommonGateway\CoreBundle\Service\CacheService;
use CommonGateway\CoreBundle\Service\GatewayResourceService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;

class VrijBrpService
{


    public function __construct(
        private readonly CacheService           $cacheService,
        private readonly GatewayResourceService $resourceService,
        private readonly EntityManagerInterface $entityManager,
    ) {

    }//end __construct()


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


    function arrayRecursiveDiff($aArray1, $aArray2)
    {
        $aReturn = [];

        foreach ($aArray1 as $mKey => $mValue) {
            if (array_key_exists($mKey, $aArray2)) {
                if (is_array($mValue)) {
                    $aRecursiveDiff = $this->arrayRecursiveDiff($mValue, $aArray2[$mKey]);
                    if (count($aRecursiveDiff)) {
                        $aReturn[$mKey] = $aRecursiveDiff;
                    }
                } else {
                    if ($mValue != $aArray2[$mKey]) {
                        $aReturn[$mKey] = $mValue;
                    }
                }
            } else {
                $aReturn[$mKey] = $mValue;
            }
        }

        return $aReturn;

    }//end arrayRecursiveDiff()


    public function createNotification(array $data, array $config): array
    {
        $object = $data['object'];

        if ($object instanceof ObjectEntity) {
            $objectData = $object->toArray(['embedded' => true, 'user' => $this->cacheService->getObjectUser(objectEntity: $object)]);

            $now = new DateTime();
            $message = [
                'kanaal'       => 'zaak.status.created',
                'hoofdobject'  => $objectData['zaak'],
                'resource'     => 'Zaak',
                'resourceUrl'  => $objectData['zaak'],
                'actie'        => 'create',
                'aanmaakdatum' => $now->format('c'),
            ];
            $schema = $this->resourceService->getSchema(
                reference: 'https://zgw.opencatalogi.nl/schema/nrc.message.schema.json',
                pluginName: 'common-gateway/vrijbrp-to-zgw-bundle'
            );
            $messageObject = new ObjectEntity(entity: $schema);
            $messageObject->hydrate($message);
            $this->entityManager->persist($messageObject);

        }//end if

        return $data;

    }//end createNotification()


}//end class
