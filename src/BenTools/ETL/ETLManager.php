<?php
namespace BenTools\ETL;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ETLManager implements LoggerAwareInterface, EventDispatcherInterface {

    use LoggerAwareTrait;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var float
     */
    protected $start;

    /**
     * @var float
     */
    protected $duration = 0.00;

    /**
     * @var ETLBag[]
     */
    protected $etlBags = [];

    /**
     * @var ETLBag
     */
    protected $currentEtl;

    /**
     * ETLManager constructor.
     * @param EventDispatcherInterface $eventDispatcher
     * @param LoggerInterface $logger
     * @param string $name
     */
    public function __construct(EventDispatcherInterface $eventDispatcher, LoggerInterface $logger, $name = null) {
        $this->eventDispatcher = $eventDispatcher;
        $this->logger          = $logger;
        $this->name            = $name;
    }

    /**
     * Run the ETLs.
     * @return $this
     */
    public function run() {

        $this->logger->info('Starting ETL.');
        $this->start    =   microtime(true);

        $this->dispatch(ETLEvent::ETL_START, new ETLEvent());

        foreach ($this->etlBags AS $etlBag) {

            $this->setCurrentEtl($etlBag);
            $context = $etlBag->getContext();

            while ($input   =   $etlBag->getExtractor()->extract($etlBag->getContext())) {

                #################
                #    EXTRACT    #
                #################

                $this->logger->info('Extracting data...');
                $this->dispatch(ETLEvent::AFTER_EXTRACT, new ETLEvent($etlBag));

                # Should we skip this row ?
                if ($context->shouldSkip()) {
                    $this->logger->debug('Skipping...', is_array($input) ? $input : []);
                    $context->shouldSkip(false);
                    continue;
                }
                # Should we stop the ETL ?
                elseif ($context->shouldBreak()) {
                    $this->logger->debug('Breaking...', is_array($input) ? $input : []);
                    break;
                }
                # Should we stop everything ?
                elseif ($context->shouldHalt()) {
                    $this->logger->debug('Aborting...', is_array($input) ? $input : []);
                    break 2;
                }
                $this->logger->debug('Data Extracted.', is_array($input) ? $input : []);


                #################
                #   TRANSFORM   #
                #################

                $this->logger->info(sprintf('Transforming data %s...', $context->getIdentifier() ? ' for ' . $context->getIdentifier() : ''));

                $this       ->  dispatch(ETLEvent::BEFORE_TRANSFORM, new ETLEvent($etlBag));
                $etlBag        ->  getTransformer()->transform($input, $etlBag->getContext());
                $this       ->  dispatch(ETLEvent::AFTER_TRANSFORM, new ETLEvent($etlBag));

                # Should we skip this row ?
                if ($context->shouldSkip()) {
                    $this->logger->debug('Skipping...', is_array($context->getTransformedData()) ? $context->getTransformedData() : []);
                    $context->shouldSkip(false);
                    continue;
                }
                # Should we stop the ETL ?
                elseif ($context->shouldBreak()) {
                    $this->logger->debug('Breaking...', is_array($context->getTransformedData()) ? $context->getTransformedData() : []);
                    break;
                }
                # Should we stop everything ?
                elseif ($context->shouldHalt()) {
                    $this->logger->debug('Aborting...', is_array($context->getTransformedData()) ? $context->getTransformedData() : []);
                    break 2;
                }
                $this->logger->debug('Data transformed.', is_array($context->getTransformedData()) ? $context->getTransformedData() : []);

                #################
                #     LOAD      #
                #################

                $this   ->  logger->info(sprintf('Loading data%s...', $context->getIdentifier() ? ' for ' . $context->getIdentifier() : ''));
                $this   ->  dispatch(ETLEvent::BEFORE_LOAD, new ETLEvent($etlBag));
                $etlBag    ->  getLoader()->load($context->getTransformedData(), $etlBag->getContext());
                $this   ->  dispatch(ETLEvent::AFTER_LOAD, new ETLEvent($etlBag));

                # Should we skip this row ?
                if ($context->shouldSkip()) {
                    $this->logger->debug('Skipping...', is_array($context->getTransformedData()) ? $context->getTransformedData() : []);
                    $context->shouldSkip(false);
                    continue;
                }
                # Should we stop the ETL ?
                elseif ($context->shouldBreak()) {
                    $this->logger->debug('Breaking...', is_array($context->getTransformedData()) ? $context->getTransformedData() : []);
                    break;
                }
                # Should we stop everything ?
                elseif ($context->shouldHalt()) {
                    $this->logger->debug('Aborting...', is_array($context->getTransformedData()) ? $context->getTransformedData() : []);
                    break 2;
                }

                # Should we flush the buffered records ?
                if ($context->shouldFlush()) {
                    $this->dispatch(ETLEvent::BEFORE_FLUSH, new ETLEvent($etlBag));
                    $this->logger->debug('Flushing...', $context->getTransformedData());
                    $etlBag->getLoader()->flush($etlBag->getContext());
                    $context->shouldFlush(false);
                    $this->dispatch(ETLEvent::AFTER_FLUSH, new ETLEvent($etlBag));
                }
            }

            #################
            #     FLUSH     #
            #################

            # Should we halt without flushing ?
            if ($context->shouldHalt()) {
                $this->logger->debug('Data won\'t be flushed.');
            }
            # Default behaviour : flush buffered records
            else {
                $this->logger->info('Flushing data...');
                $this->dispatch(ETLEvent::BEFORE_FLUSH, new ETLEvent($etlBag));
                $etlBag->getLoader()->flush($etlBag->getContext());
                $this->dispatch(ETLEvent::AFTER_FLUSH, new ETLEvent($etlBag));
            }

        }

        # Over.
        $this           ->  dispatch(ETLEvent::ETL_END, new ETLEvent());
        $this->duration =   round(microtime(true) - $this->start, 3);
        $this->logger   ->  info(sprintf("ETL completed in %ss.", $this->getDuration()));

        return $this;
    }

    /**
     * @return ETLBag[]
     */
    public function getEtlBags() {
        return $this->etlBags;
    }

    /**
     * @param ETLBag[] $etlBags
     * @return $this - Provides Fluent Interface
     */
    public function setEtlBags(array $etlBags) {
        $this->etlBags = [];
        foreach ($etlBags AS $etl)
            $this->addEtl($etl);
        return $this;
    }

    /**
     * @param ETLBag $ETLBag
     * @return $this
     */
    public function addEtl(ETLBag $ETLBag) {
        $this->etlBags[] =   $ETLBag;
        return $this;
    }

    /**
     * @return $this
     */
    public function clearEtls() {
        $this->etlBags = [];
        return $this;
    }

    /**
     * @param ETLBag $currentEtl
     * @return $this - Provides Fluent Interface
     */
    protected function setCurrentEtl(ETLBag $currentEtl) {
        $this->currentEtl = $currentEtl;
        return $this;
    }

    /**
     * @return ETLBag
     */
    public function getCurrentEtl() {
        return $this->currentEtl;
    }

    /**
     * @return float
     */
    public function getDuration() {
        return $this->duration;
    }

    /**
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @param string $name
     * @return $this - Provides Fluent Interface
     */
    public function setName($name) {
        $this->name = $name;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function dispatch($eventName, Event $event = null) {
        return $this->eventDispatcher->dispatch($eventName, $event);
    }

    /**
     * @inheritdoc
     */
    public function addListener($eventName, $listener, $priority = 0) {
        return $this->eventDispatcher->addListener($eventName, $listener, $priority);
    }

    /**
     * @inheritdoc
     */
    public function addSubscriber(EventSubscriberInterface $subscriber) {
        return $this->eventDispatcher->addSubscriber($subscriber);
    }

    /**
     * @inheritdoc
     */
    public function removeListener($eventName, $listener) {
        return $this->eventDispatcher->removeListener($eventName, $listener);
    }

    /**
     * @inheritdoc
     */
    public function removeSubscriber(EventSubscriberInterface $subscriber) {
        return $this->eventDispatcher->removeSubscriber($subscriber);
    }

    /**
     * @inheritdoc
     */
    public function getListeners($eventName = null) {
        return $this->eventDispatcher->getListeners($eventName);
    }

    /**
     * @inheritdoc
     */
    public function getListenerPriority($eventName, $listener) {
        return $this->eventDispatcher->getListenerPriority($eventName, $listener);
    }

    /**
     * @inheritdoc
     */
    public function hasListeners($eventName = null) {
        return $this->eventDispatcher->hasListeners($eventName);
    }

    /**
     * @return EventDispatcherInterface
     */
    public function getEventDispatcher() {
        return $this->eventDispatcher;
    }

}