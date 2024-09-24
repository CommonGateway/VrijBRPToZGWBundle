<?php

namespace CommonGateway\VrijBRPToZGWBundle\Service;

use App\Entity\Synchronization;
use CommonGateway\CoreBundle\Service\CallService;
use CommonGateway\CoreBundle\Service\GatewayResourceService;
use CommonGateway\CoreBundle\Service\SynchronizationService;
use Dompdf\Exception;

class VrijBrpToZgwService
{
    public function __construct(
        private GatewayResourceService $resourceService,
        private CallService $callService,
        private SynchronizationService $synchronizationService,
    )
    {

    }
    
    public function getDossiers(array $configuration, Source $source): array
    {
        

        if (isset($configuration['endpoint']) === false) {
            $configuration['endpoint'] = '/v1/api/dossiers/search';
        }
        if (isset($configuration['method']) === false) {
            $configuration['method'] = 'POST';
        }
        if (isset($configuration['types']) === false) {
            $configuration['types'] = [
                'intra_mun_relocation',
                'inter_mun_relocation',
            ];
        }

        $response = $this->callService->call(source: $source, endpoint: $configuration['endpoint'], method: $configuration['method'], config: ['json' => ['types' => $configuration['types']]]);

        $result = $this->callService->decodeResponse(source: $source, response: $response);

        if(isset($result['result']['content']) === true) {
            return $result['result']['content'];
        }
        
        throw new \Exception('No cases found');
    }
    
    
    public function syncVrijBrpHandler (array $data, array $configuration): array
    {
        $source = $this->resourceService->getSource(reference: $configuration['source'], pluginName: "common-gateway/vrijbrp-to-zgw-bundle");
        $source = $this->resourceService->getSchema(reference: $configuration['schema'], pluginName: "common-gateway/vrijbrp-to-zgw-bundle");
        
        try {
            $dossiers = $this->getDossiers(configuration: $configuration, source: $source);
            foreach($dossiers as $dossier) {
                //@TODO: Fetch synchronization for dossier (if it exists)
                $synchronization = new Synchronization();
                $synchronization->setSource($source);
                $synchronization->setEntity($schema);
                
                $this->synchronizationService->synchronizeTemp(synchronization: $synchronization, objectArray: $dossier);
            }
        } catch (Exception $exception) {
            //@TODO: log exception and end synchronization.
        }

        return $data;
    }
}
