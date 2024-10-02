<?php

namespace CommonGateway\VrijBRPToZGWBundle\ActionHandler;

use CommonGateway\VrijBRPToZGWBundle\Service\NewSynchronizationService;
use CommonGateway\VrijBRPToZGWBundle\Service\VrijBrpService;
use CommonGateway\CoreBundle\ActionHandler\ActionHandlerInterface;

class NotificationUpdateHandler implements ActionHandlerInterface
{

    public function __construct(
        private readonly VrijBrpService $vrijBrpService,
    ) {

    }//end __construct()


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
            'properties'  => [],
        ];

    }//end getConfiguration()


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
        return $this->vrijBrpService->createStatusNotification($data, $configuration);

    }//end run()


}//end class
