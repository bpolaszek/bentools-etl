<?php

namespace BenTools\ETL\Loader;

use BenTools\ETL\Etl;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Util\ClassUtils;
use Psr\Log\LoggerAwareTrait;

final class DoctrineORMLoader implements LoaderInterface
{

    use LoggerAwareTrait;

    /**
     * @var ManagerRegistry
     */
    private $managerRegistry;

    /**
     * @var ObjectManager[]
     */
    private $objectManagers = [];

    /**
     * DoctrineORMLoader constructor.
     *
     * @param ManagerRegistry      $managerRegistry
     */
    public function __construct(ManagerRegistry $managerRegistry)
    {
        $this->managerRegistry = $managerRegistry;
    }

    /**
     * @inheritDoc
     */
    public function init(): void
    {
    }

    /**
     * @inheritDoc
     */
    public function load(\Generator $entities, $key, Etl $etl): void
    {
        foreach ($entities as $entity) {
            if (!is_object($entity)) {
                throw new \InvalidArgumentException("The transformed data should return a generator of entities.");
            }

            $className = ClassUtils::getClass($entity);
            $objectManager = $this->managerRegistry->getManagerForClass($className);
            if (null === $objectManager) {
                throw new \RuntimeException(sprintf("Unable to locate Doctrine manager for class %s.", $className));
            }

            $objectManager->persist($entity);

            if (!in_array($objectManager, $this->objectManagers)) {
                $this->objectManagers[] = $objectManager;
            }
        }
    }


    /**
     * @inheritDoc
     */
    public function rollback(): void
    {
        foreach ($this->objectManagers as $objectManager) {
            $objectManager->clear();
        }
        $this->objectManagers = [];
    }

    /**
     * @inheritDoc
     */
    public function commit(bool $partial): void
    {
        foreach ($this->objectManagers as $objectManager) {
            $objectManager->flush();
        }
        $this->objectManagers = [];
    }
}
