<?php

namespace CommonGateway\VrijBRPToZGWBundle\Service;

use Adbar\Dot;
use App\Entity\Gateway as Source;
use App\Entity\ObjectEntity;
use App\Entity\Synchronization;
use App\Service\SynchronizationService;
use CommonGateway\CoreBundle\Service\CallService;
use CommonGateway\CoreBundle\Service\GatewayResourceService;
use CommonGateway\CoreBundle\Service\MappingService;
use DateInterval;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Log\LoggerInterface;
use Twig\Error\LoaderError;
use Twig\Error\SyntaxError;


/**
 * Service to synchronize resources between gateway and external sources.
 *
 * This service provides synchronization functionality to synchronize between sources and the gateway.
 *
 * @author Robert Zondervan, Barry Brands, Ruben van der Linde
 * @license EUPL<github.com/ConductionNL/contactcatalogus/blob/master/LICENSE.md>
 *
 * @category Service
 */
class NewSynchronizationService
{
    public function __construct(
        private readonly GatewayResourceService $resourceService,
        private readonly CallService            $callService,
        private readonly SynchronizationService $synchronizationService,
        private readonly LoggerInterface        $synchronizationLogger,
        private readonly EntityManagerInterface $entityManager,
        private readonly MappingService         $mappingService,
    )
    {

    }

    /**
     * Executes the synchronization from source to gateway.
     * Slightly edited clone of the SynchronizationService in the gateway.
     *
     * @param Synchronization $synchronization The synchronization to update
     * @param array           $sourceObject    The object in the source
     * @param bool            $unsafe          Unset attributes that are not included in the hydrator array when calling the hydrate function
     *
     * @throws GuzzleException
     * @throws LoaderError
     * @throws SyntaxError
     *
     * @return Synchronization The updated synchronization
     */
    public function synchronizeFromSource(Synchronization $synchronization, array $sourceObject = [], bool $unsafe = false): Synchronization
    {
        $this->synchronizationLogger->info("handleSync for Synchronization with id = {$synchronization->getId()->toString()}");

        //create new object if no object exists
        if (!$synchronization->getObject()) {
            isset($this->io) && $this->io->text('creating new objectEntity');
            $this->synchronizationLogger->info('creating new objectEntity');
            $object = new ObjectEntity($synchronization->getEntity());
            $object->addSynchronization($synchronization);
            $this->entityManager->persist($object);
            $this->entityManager->persist($synchronization);
            $oldDateModified = null;
        } else {
            $oldDateModified = $synchronization->getObject()->getDateModified()->getTimestamp();
        }
        $sourceObject = $sourceObject ?: $this->synchronizationService->getSingleFromSource($synchronization);

        if ($sourceObject === null) {
            $this->synchronizationLogger->warning("Can not handle Synchronization with id = {$synchronization->getId()->toString()} if \$sourceObject === null");

            return $synchronization;
        }

        // Let check
        $now = new DateTime();
        $synchronization->setLastChecked($now);

        $sha = hash('sha256', json_encode($sourceObject));

        // Checking if data on source has changed.
        if($synchronization->getSha() === $sha) {
            return $synchronization;
        }

        // Counter
        $counter = $synchronization->getTryCounter() + 1;
        if ($counter > 10000) {
            $counter = 10000;
        }
        $synchronization->setTryCounter($counter);

        // Set dont try before, expensional so in minutes  1,8,27,64,125,216,343,512,729,1000
        $addMinutes = pow($counter, 3);
        if ($synchronization->getDontSyncBefore()) {
            $dontTryBefore = $synchronization->getDontSyncBefore()->add(new DateInterval('PT'.$addMinutes.'M'));
        } else {
            $dontTryBefore = new DateTime();
        }
        $synchronization->setDontSyncBefore($dontTryBefore);

        if ($synchronization->getMapping()) {
            $sourceObject = $this->mappingService->mapping($synchronization->getMapping(), $sourceObject);
        }
        $synchronization->getObject()->hydrate($sourceObject, $unsafe);

        $synchronization->setSha($sha);

        $this->entityManager->persist($synchronization->getObject());
        $this->entityManager->persist($synchronization);

        if ($oldDateModified !== $synchronization->getObject()->getDateModified()->getTimestamp()) {
            $date = new DateTime();
                isset($this->io) ?? $this->io->text("set new dateLastChanged to {$date->format('d-m-YTH:i:s')}");
            $synchronization->setLastSynced(new DateTime());
            $synchronization->setTryCounter(0);
        } else {
                isset($this->io) ?? $this->io->text("lastSynced is still {$synchronization->getObject()->getDateModified()->format('d-m-YTH:i:s')}");
        }

        return $synchronization;
    }

    /**
     * Fetch data from source in a way that is as abstract as possible at this time.
     *
     * @param array $configuration
     * @param Source $source
     * @return array
     * @throws Exception
     */
    public function getResults(array $configuration, Source $source): array
    {
        $response = $this->callService->call(source: $source, endpoint: $configuration['endpoint'], method: $configuration['method'], config: ['json' => ['types' => $configuration['types']]]);

        $result = $this->callService->decodeResponse(source: $source, response: $response, contentType: $configuration['content-type'] ?? 'application/json');

        $resultDot = new Dot($result);


        if($resultDot->has(keys: $configuration['resultsPath']) === true) {
            $return = $resultDot->get(key: $configuration['resultsPath']);
            if($return instanceof Dot) {
                return $return->jsonSerialize();
            } else if (is_array($return)) {
                return $return;
            }
        }

        throw new Exception('No cases found');
    }


    /**
     * This function is designed to in time replace the existing syncCollectionHandler.
     * At the moment it depends on the in-gateway SynchronizationService, and is one way with the source as the leading version.
     *
     * @param array $data
     * @param array $configuration
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function synchronizeCollectionHandler (array $data, array $configuration): array
    {
        $source = $this->resourceService->getSource(reference: $configuration['source'], pluginName: "common-gateway/vrijbrp-to-zgw-bundle");
        $schema = $this->resourceService->getSchema(reference: $configuration['schema'], pluginName: "common-gateway/vrijbrp-to-zgw-bundle");

        if(isset($configuration['mapping']) === true) {
            $mapping = $this->resourceService->getMapping(reference: $configuration['mapping'], pluginName: "common-gateway/vrijbrp-to-zgw-bundle");
        }

        try {
            $dossiers = $this->getResults(configuration: $configuration, source: $source);
        } catch (Exception $exception) {
            $this->synchronizationLogger->warning(message: $exception->getMessage(), context: ['plugin' => 'common-gateway/vrijbrp-to-zgw-bundle']);
            return $data;
        }

        foreach($dossiers as $dossier) {
            $dossierDot = new Dot($dossier);

            $synchronization = $this->synchronizationService->findSyncBySource(source: $source, entity: $schema, sourceId: $dossierDot[$configuration['idLocation']], endpoint: $configuration['endpoint']);

            if($synchronization->getMapping() === null && isset($mapping) === true) {
                $synchronization->setMapping($mapping);
            }

            try{
                $this->synchronizeFromSource(synchronization: $synchronization, sourceObject: $dossier);
            } catch (Exception $exception) {
                $this->synchronizationLogger->error(message: $exception->getMessage(), context: ['plugin' => 'common-gateway/vrijbrp-to-zgw-bundle']);
            }
        }

        return $data;
    }
}
