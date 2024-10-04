<?php

namespace CommonGateway\VrijBRPToZGWBundle\ActionHandler;

use CommonGateway\VrijBRPToZGWBundle\Service\NewSynchronizationService;
use CommonGateway\VrijBRPToZGWBundle\Service\VrijBrpService;
use CommonGateway\CoreBundle\ActionHandler\ActionHandlerInterface;

/**
 * Handler for firing notifications for new statuses.
 *
 * @author  Robert Zondervan, Barry Brands, Ruben van der Linde
 * @license EUPL<github.com/ConductionNL/contactcatalogus/blob/master/LICENSE.md>
 *
 * @category ActionHandler
 */
class NotificationUpdateHandler implements ActionHandlerInterface
{


    /**
     * @param VrijBrpService $vrijBrpService The VrijBRP Service
     */
    public function __construct(
        private readonly VrijBrpService $vrijBrpService,
    ) {

    }//end __construct()


    /**
     *  This function returns the requered configuration as a [json-schema](https://json-schema.org/) array.
     *
     * @throws array a [json-schema](https://json-schema.org/) that this  action should comply to
     * @return array The default configuration options of this action handler.
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
     * @return array The updated data array.
     */
    public function run(array $data, array $configuration): array
    {
        return $this->vrijBrpService->createStatusNotification($data, $configuration);

    }//end run()


}//end class
