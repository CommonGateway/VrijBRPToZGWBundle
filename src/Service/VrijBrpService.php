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
        private readonly CacheService $cacheService,
        private readonly GatewayResourceService $resourceService,
        private readonly EntityManagerInterface $entityManager,
    ) {

    }

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

    function arrayRecursiveDiff($aArray1, $aArray2) {
        $aReturn = array();

        foreach ($aArray1 as $mKey => $mValue) {
            if (array_key_exists($mKey, $aArray2)) {
                if (is_array($mValue)) {
                    $aRecursiveDiff = $this->arrayRecursiveDiff($mValue, $aArray2[$mKey]);
                    if (count($aRecursiveDiff)) { $aReturn[$mKey] = $aRecursiveDiff; }
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
    }

    public function createNotification(array $data, array $config): array
    {
        var_dump('hello');


        $object = $data['object'];

        if($object instanceof ObjectEntity) {
            $data    = $object->toArray(['embedded' => true, 'user' => $this->cacheService->getObjectUser(objectEntity: $object)]);
            $oldData = $this->cacheService->getObject($object->getId());

            $diff = $this->arrayRecursiveDiff($data, $oldData);

            if(array_key_exists(key: 'status', array: $diff) === true) {
                $now = new DateTime();
                $message = [
                    'kanaal'       => 'zaak.status.created',
                    'hoofdobject'  => $data['url'],
                    'resource'     => 'Zaak',
                    'resourceUrl'  => $data['url'],
                    'actie'        => 'create',
                    'aanmaakdatum' => $now->format('c'),
                ];
                $schema        = $this->resourceService->getSchema(
                    reference:'https://zgw.opencatalogi.nl/schema/nrc.message.schema.json',
                    pluginName: 'common-gateway/vrijbrp-to-zgw-bundle'
                );
                $messageObject = new ObjectEntity(entity: $schema);
                $messageObject->hydrate($message);
                $this->entityManager->persist($messageObject);
            }
        }

        return $data;

    }


}//end class
