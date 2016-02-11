<?php
namespace BenTools\ETL\Loader;

use Doctrine\ORM\EntityManager;
use Knp\ETL\ContextInterface;
use Knp\ETL\LoaderInterface;

class DoctrineLoader implements LoaderInterface {

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var array
     */
    protected $entities = [];

    /**
     * DoctrineLoader constructor.
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager) {
        $this->entityManager = $entityManager;
    }

    /**
     * @inheritDoc
     */
    public function load($data, ContextInterface $context) {
        $this->entities[] = $data;
        $this->entityManager->persist($data);
        if ($context->shouldFlush())
            $this->flush($context);
    }

    /**
     * @inheritDoc
     */
    public function flush(ContextInterface $context) {
        if ($this->entities)
            $this->entityManager->flush($this->entities);
        $this->clear($context);
    }

    /**
     * @inheritDoc
     */
    public function clear(ContextInterface $context) {
        $this->entities = [];
    }

}