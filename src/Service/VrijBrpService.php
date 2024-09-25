<?php

namespace CommonGateway\VrijBRPToZGWBundle\Service;

class VrijBrpService
{


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


}//end class
