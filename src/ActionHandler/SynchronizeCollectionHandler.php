<?php

namespace CommonGateway\VrijBRPToZGWBundle\ActionHandler;

use App\ActionHandler\ActionHandlerInterface;
use CommonGateway\VrijBRPToZGWBundle\Service\NewSynchronizationService;
use CommonGateway\VrijBRPToZGWBundle\Service\VrijBrpService;

class SynchronizeCollectionHandler implements ActionHandlerInterface
{

    /**
     * @param NewSynchronizationService $synchronizationService
     */
    public function __construct(
        private readonly NewSynchronizationService $synchronizationService,
        private readonly VrijBrpService            $vrijBrpService,
    )
    {
    }

    /**
     *  This function returns the requered configuration as a [json-schema](https://json-schema.org/) array.
     *
     * @throws array a [json-schema](https://json-schema.org/) that this  action should comply to
     */
    public function getConfiguration(): array
    {
        return [
            '$id'         => 'https://commongateway.nl/ActionHandler/SynchronizationCollectionHandler.ActionHandler.json',
            '$schema'     => 'https://docs.commongateway.nl/schemas/ActionHandler.schema.json',
            'title'       => 'SynchronizationCollectionHandler',
            'description' => '',
            'required'    => [],
            'properties'  => [
            ],
        ];
    }

    /**
     * Run the actual business logic in the appropriate server.
     *
     * @param array $data          The data from the call
     * @param array $configuration The configuration of the action
     *
     * @return array
     */
    public function run(array $data, array $configuration): array
    {
        $configuration = $this->vrijBrpService->setVrijBRPDefaults($configuration);

        return $this->synchronizationService->synchronizeCollectionHandler($data, $configuration);
    }
}
