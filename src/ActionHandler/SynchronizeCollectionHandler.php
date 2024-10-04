<?php

namespace CommonGateway\VrijBRPToZGWBundle\ActionHandler;

use CommonGateway\VrijBRPToZGWBundle\Service\NewSynchronizationService;
use CommonGateway\VrijBRPToZGWBundle\Service\VrijBrpService;
use CommonGateway\CoreBundle\ActionHandler\ActionHandlerInterface;

/**
 * Handler for synchronizing collections from external sources.
 *
 * @author  Robert Zondervan, Barry Brands, Ruben van der Linde
 * @license EUPL<github.com/ConductionNL/contactcatalogus/blob/master/LICENSE.md>
 *
 * @category ActionHandler
 */
class SynchronizeCollectionHandler implements ActionHandlerInterface
{


    /**
     * @param NewSynchronizationService $syncService
     * @param VrijBrpService            $vrijBrpService
     */
    public function __construct(
        private readonly NewSynchronizationService $syncService,
        private readonly VrijBrpService $vrijBrpService,
    ) {

    }//end __construct()


    /**
     *  This function returns the requered configuration as a [json-schema](https://json-schema.org/) array.
     *
     * @throws array a [json-schema](https://json-schema.org/) that this  action should comply to
     *
     * @return array The configuration of the handler.
     */
    public function getConfiguration(): array
    {
        return [
            '$id'         => 'https://commongateway.nl/ActionHandler/SynchronizationCollectionHandler.ActionHandler.json',
            '$schema'     => 'https://docs.commongateway.nl/schemas/ActionHandler.schema.json',
            'title'       => 'SynchronizationCollectionHandler',
            'description' => '',
            'required'    => [
                'source',
                'schema',
                'endpoint',
                'idField',
                'resultsPath',
            ],
            'properties'  => [
                'source'      => [
                    'type'        => 'string',
                    'description' => 'The source where the publication belongs to.',
                    'example'     => 'https://commongateway.woo.nl/source/buren.openwoo.source.json',
                    'required'    => true,
                ],
                'schema'      => [
                    'type'        => 'string',
                    'description' => 'The publication schema.',
                    'example'     => 'https://commongateway.nl/woo.publicatie.schema.json',
                    'reference'   => 'https://commongateway.nl/woo.publicatie.schema.json',
                    'required'    => true,
                ],
                'mapping'     => [
                    'type'        => 'string',
                    'description' => 'The mapping for open woo to publication.',
                    'example'     => 'https://commongateway.nl/mapping/woo.openWooToWoo.mapping.json',
                    'reference'   => 'https://commongateway.nl/mapping/woo.openWooToWoo.mapping.json',
                    'required'    => false,
                ],
                'endpoint'    => [
                    'type'        => 'string',
                    'description' => 'The endpoint of the source.',
                    'example'     => '/wp-json/owc/openwoo/v1/items',
                    'required'    => true,
                ],
                'idField'     => [
                    'type'        => 'string',
                    'description' => 'Dot-array location of the field that contains the id',
                    'example'     => 'dossierId',
                    'required'    => true,
                ],
                'resultsPath' => [
                    'type'        => 'string',
                    'description' => 'Dot-array location of the field that contains the results',
                    'example'     => 'dossierId',
                    'required'    => true,
                ],
                'body'        => [
                    'type'        => 'array',
                    'description' => 'Body that will be sent to the source if necessary.',
                    'example'     => 'dossierId',
                    'required'    => false,
                ],
            ],

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
        $configuration = $this->vrijBrpService->setVrijBRPDefaults($configuration);

        return $this->syncService->synchronizeCollectionHandler($data, $configuration);

    }//end run()


}//end class
