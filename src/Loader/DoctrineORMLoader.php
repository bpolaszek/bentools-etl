<?php

namespace BenTools\ETL\Loader;

use BenTools\ETL\Context\ContextElementInterface;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class DoctrineORMLoader implements FlushableLoaderInterface
{

    use LoggerAwareTrait;

    /**
     * @var ManagerRegistry
     */
    private $managerRegistry;

    /**
     * @var int
     */
    private $flushEvery = 1;

    /**
     * @var int
     */
    private $counter = 0;

    /**
     * @var ObjectManager[]
     */
    private $objectManagers = [];

    /**
     * DoctrineORMLoader constructor.
     *
     * @param ManagerRegistry      $managerRegistry
     * @param int                  $flushEvery
     * @param LoggerInterface|null $logger
     */
    public function __construct(ManagerRegistry $managerRegistry, int $flushEvery = 1, LoggerInterface $logger = null)
    {
        $this->managerRegistry = $managerRegistry;
        $this->flushEvery = $flushEvery;
        $this->logger = $logger ?? new NullLogger();
    }

    /**
     * @param int $flushEvery
     * @return $this - Provides Fluent Interface
     */
    public function setFlushEvery(int $flushEvery)
    {
        $this->flushEvery = $flushEvery;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function shouldFlushAfterLoad(): bool
    {
        return 0 !== $this->flushEvery // Otherwise we'll wait on an explicit flush() call
            && 0 === ($this->counter % $this->flushEvery);
    }

    /**
     * @inheritDoc
     */
    public function flush(): void
    {
        foreach ($this->objectManagers as $objectManager) {
            $objectManager->flush();
        }
        $this->logger->debug(sprintf('Doctrine: flushed %d entities', $this->counter));
        $this->objectManagers = [];
        $this->counter = 0;
    }

    /**
     * @inheritDoc
     */
    public function __invoke(ContextElementInterface $element): void
    {
        $entity = $element->getData();

        if (!is_object($entity)) {
            throw new \InvalidArgumentException("The transformed data should return an entity object.");
        }

        $className = get_class($entity);
        $objectManager = $this->managerRegistry->getManagerForClass($className);
        if (null === $objectManager) {
            throw new \RuntimeException(sprintf("Unable to locate Doctrine manager for class %s.", $className));
        }

        $objectManager->persist($entity);
        $this->logger->debug(
            'Loading entity',
            [
                'class' => $className,
                'id' => $element->getId(),
                'data', $element->getData()
            ]
        );

        if (!in_array($objectManager, $this->objectManagers)) {
            $this->objectManagers[] = $objectManager;
        }

        if (1 === $this->flushEvery) {
            $this->flush();
        }

        $this->counter++;
    }
}
