<?php
namespace BenTools\ETL\Loader;

use BenTools\ETL\Interfaces\ContextInterface;
use BenTools\ETL\Interfaces\LoaderInterface;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManager;

class DoctrineLoader implements LoaderInterface {

    /**
     * @var ManagerRegistry
     */
    protected $managerRegistry;

    /**
     * @var array
     */
    protected $entities = [];

    /**
     * DoctrineLoader constructor.
     * @param ManagerRegistry $managerRegistry
     */
    public function __construct(ManagerRegistry $managerRegistry) {
        $this->managerRegistry = $managerRegistry;
    }

    /**
     * @inheritDoc
     */
    public function load($entity, ContextInterface $context) {
        $em           = $this->getEntityManagerOf($entity);
        $emIdentifier = spl_object_hash($em);
        $em->persist($entity);

        if (!isset($this->entities[$emIdentifier]))
            $this->entities[$emIdentifier] = ['em' => $em];

        $this->entities[$emIdentifier]['entities'][] = $entity;

        if ($context->shouldFlush())
            $this->flush($context);
    }

    /**
     * @inheritDoc
     */
    public function flush(ContextInterface $context) {
        if ($this->entities) {
            foreach ($this->entities AS $emIdentifier => $entityCollection) {
                $em       = $entityCollection['em'];
                $entities = $entityCollection['entities'];
                $em->flush($entities);
            }
        }
        $this->clear($context);
    }

    /**
     * @inheritDoc
     */
    public function clear(ContextInterface $context) {
        $this->entities = [];
    }

    /**
     * @return ManagerRegistry
     */
    public function getManagerRegistry() {
        return $this->managerRegistry;
    }

    /**
     * @param ManagerRegistry $managerRegistry
     * @return $this - Provides Fluent Interface
     */
    public function setManagerRegistry(ManagerRegistry $managerRegistry = null) {
        $this->managerRegistry = $managerRegistry;
        return $this;
    }

    /**
     * @return ObjectManager|EntityManager
     */
    public function getEntityManager($name = null) {
        return is_null($name) ? $this->managerRegistry->getManager($this->managerRegistry->getDefaultManagerName()) : $this->managerRegistry->getManager($name);
    }

    /**
     * @param $nameOrObject
     * @return ObjectManager|EntityManager
     */
    public function getEntityManagerOf($nameOrObject) {
        return $this->managerRegistry->getManagerForClass($this->resolveNameOrObject($nameOrObject));
    }

    /**
     * @param $nameOrObject
     * @return string
     */
    private function resolveNameOrObject($nameOrObject) {
        switch (true) {
            case is_object($nameOrObject) && $nameOrObject instanceof \Doctrine\ORM\Proxy\Proxy:
                return get_parent_class($nameOrObject);
            case is_object($nameOrObject):
                return get_class($nameOrObject);
            default:
                return $nameOrObject;
        }
    }

    /**
     * @param $nameOrObject
     * @return ObjectRepository
     */
    public function getRepositoryOf($nameOrObject) {
        $name = $this->resolveNameOrObject($nameOrObject);
        return $this->getEntityManagerOf($name)->getRepository($name);
    }

}