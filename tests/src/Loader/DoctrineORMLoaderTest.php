<?php

namespace BenTools\ETL\Tests\Loader;

use BenTools\ETL\Context\ContextElement;
use BenTools\ETL\Event\ContextElementEvent;
use BenTools\ETL\Event\ETLEvents;
use BenTools\ETL\Event\EventDispatcher\ETLEventDispatcher;
use BenTools\ETL\Extractor\KeyValueExtractor;
use BenTools\ETL\Loader\DoctrineORMLoader;
use BenTools\ETL\Runner\ETLRunner;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use PHPUnit\Framework\TestCase;


class DoctrineORMLoaderTest extends TestCase
{
    /**
     * Fakes a Doctrine entity object.
     * @param $id
     * @param $name
     */
    private function fakeEntity($id, $name)
    {
        return new class($id, $name) {
            private $id, $name;

            public function __construct($id, $name)
            {
                $this->id   = $id;
                $this->name = $name;
            }

            public function getId()
            {
                return $this->id;
            }

            public function getName()
            {
                return $this->name;
            }

        };
    }

    /**
     * Fakes a Doctrine Entity Repository.
     * @param $className
     * @return ObjectRepository
     */
    private function fakeRepository($className): ObjectRepository
    {
        $fakeRepository = new class($className) implements ObjectRepository {

            private $storage = [];
            private $className = '';

            public function __construct($className)
            {
                $this->className = $className;
            }

            public function find($id)
            {
                return $this->storage[$id] ?? null;
            }

            public function findAll()
            {
                return array_values($this->storage);
            }

            public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
            {
                throw new \LogicException(sprintf('%s is not implemented.', __METHOD__));
            }

            public function findOneBy(array $criteria)
            {
                throw new \LogicException(sprintf('%s is not implemented.', __METHOD__));
            }

            public function getClassName()
            {
                return $this->className;
            }

            public function store($object)
            {
                $this->storage[$object->getId()] = $object;
            }

            public function remove($object)
            {
                if (false !== $k = array_search($object, $this->storage)) {
                    unset($this->storage[$k]);
                }
            }
        };

        return $fakeRepository;
    }

    /**
     * Fakes a Doctrine Object Manager.
     * @param $repositories
     * @return ObjectManager
     */
    private function fakeObjectManager($repositories): ObjectManager
    {
        $fakeManager = new class($repositories) implements ObjectManager
        {

            private $repositories = [];
            private $tmpStorage = [];

            public function __construct(array $repositories)
            {
                $this->repositories = $repositories;
            }

            /**
             * @inheritDoc
             */
            public function find($className, $id)
            {
                return $this->getRepository($className)->find($id);
            }

            public function persist($object)
            {
                $this->tmpStorage[] = $object;
            }

            public function remove($object)
            {
                $this->getRepository(get_class($object))->remove($object);
            }

            public function merge($object)
            {
                throw new \LogicException(sprintf('%s is not implemented.', __METHOD__));
            }

            public function clear($objectName = null)
            {
                throw new \LogicException(sprintf('%s is not implemented.', __METHOD__));
            }

            public function detach($object)
            {
                throw new \LogicException(sprintf('%s is not implemented.', __METHOD__));
            }

            public function refresh($object)
            {
                throw new \LogicException(sprintf('%s is not implemented.', __METHOD__));
            }

            public function flush()
            {
                foreach ($this->tmpStorage AS $o => $object) {
                    $this->getRepository(get_class($object))->store($object);
                    unset($this->tmpStorage[$o]);
                }
            }

            public function getRepository($className)
            {
                return $this->repositories[$className] ?? null;
            }

            public function getClassMetadata($className)
            {
                throw new \LogicException(sprintf('%s is not implemented.', __METHOD__));
            }

            public function getMetadataFactory()
            {
                throw new \LogicException(sprintf('%s is not implemented.', __METHOD__));
            }

            public function initializeObject($obj)
            {
                throw new \LogicException(sprintf('%s is not implemented.', __METHOD__));
            }

            public function contains($object)
            {
                $className = get_class($object);
                return null !== $this->find($className, $object->getId()) || in_array($object, $this->tmpStorage);
            }

        };

        return $fakeManager;
    }

    /**
     * Fakes a Doctrine Manager Registry.
     * @param $objectManagers
     * @return ManagerRegistry
     */
    private function fakeManagerRegistry($objectManagers): ManagerRegistry
    {
        $fakeRegistry = new class($objectManagers) implements ManagerRegistry {

            private $objectManagers = [];

            public function __construct(array $objectManagers)
            {
                $this->objectManagers = $objectManagers;
            }

            public function getDefaultConnectionName()
            {
                throw new \LogicException(sprintf('%s is not implemented.', __METHOD__));
            }

            public function getConnection($name = null)
            {
                throw new \LogicException(sprintf('%s is not implemented.', __METHOD__));
            }

            public function getConnections()
            {
                throw new \LogicException(sprintf('%s is not implemented.', __METHOD__));
            }

            public function getConnectionNames()
            {
                throw new \LogicException(sprintf('%s is not implemented.', __METHOD__));
            }

            public function getDefaultManagerName()
            {
                throw new \LogicException(sprintf('%s is not implemented.', __METHOD__));
            }

            public function getManager($name = null)
            {
                return $this->objectManagers[$name] ?? null;
            }

            public function getManagers()
            {
                return $this->objectManagers;
            }

            public function resetManager($name = null)
            {
                throw new \LogicException(sprintf('%s is not implemented.', __METHOD__));
            }

            public function getAliasNamespace($alias)
            {
                throw new \LogicException(sprintf('%s is not implemented.', __METHOD__));
            }

            public function getManagerNames()
            {
                throw new \LogicException(sprintf('%s is not implemented.', __METHOD__));
            }

            public function getRepository($persistentObject, $persistentManagerName = null)
            {
                foreach ($this->objectManagers AS $manager) {
                    if (null !== $manager->getRepository(get_class($persistentObject))) {
                        return $manager;
                    }
                }
                return null;
            }

            public function getManagerForClass($class)
            {
                foreach ($this->objectManagers AS $manager) {
                    if (null !== $manager->getRepository($class)) {
                        return $manager;
                    }
                }
                return null;
            }

        };

        return $fakeRegistry;
    }

    public function testFakeEntity()
    {
        $entity = $this->fakeEntity('foo', 'bar');
        $anotherEntity = $this->fakeEntity('bar', 'baz');
        $this->assertEquals(get_class($entity), get_class($anotherEntity));
        $this->assertEquals('foo', $entity->getId());
        $this->assertEquals('bar', $entity->getName());
        $this->assertEquals('bar', $anotherEntity->getId());
        $this->assertEquals('baz', $anotherEntity->getName());
    }

    public function testFakeRepository()
    {

        $entity = $this->fakeEntity('foo', 'bar');
        $className = get_class($entity);

        $repository = $this->fakeRepository($className);
        $this->assertNull($repository->find($entity->getId()));

        $repository->store($entity);
        $this->assertNotNull($repository->find($entity->getId()));
        $this->assertSame($repository->find($entity->getId()), $entity);

        $repository->remove($entity);
        $this->assertNull($repository->find($entity->getId()));
    }

    public function testFakeObjectManager()
    {
        $entity = $this->fakeEntity('foo', 'bar');
        $className = get_class($entity);
        $repository = $this->fakeRepository($className);
        $em = $this->fakeObjectManager([$className => $repository]);

        $this->assertNull($em->find($className, $entity->getId()));
        $this->assertFalse($em->contains($entity));

        // Test persistence
        $em->persist($entity);
        $this->assertNull($em->find($className, $entity->getId()));
        $this->assertTrue($em->contains($entity));

        // Test Flush
        $em->flush();
        $this->assertNotNull($em->find($className, $entity->getId()));
        $this->assertTrue($em->contains($entity));
    }

    public function testFakeRegistry()
    {
        $entity = $this->fakeEntity('foo', 'bar');
        $className = get_class($entity);
        $repository = $this->fakeRepository($className);
        $em = $this->fakeObjectManager([$className => $repository]);
        $registry = $this->fakeManagerRegistry(['default' => $em]);
        $this->assertSame($registry->getManagerForClass($className), $em);
    }

    public function testLoaderWithDefaultSettings()
    {
        $entity = $this->fakeEntity('foo', 'bar');
        $anotherEntity = $this->fakeEntity('bar', 'baz');
        $className = get_class($entity);

        $registry = $this->fakeManagerRegistry(
            [
                'default' => $em = $this->fakeObjectManager([
                    $className => $repository = $this->fakeRepository($className)
                ])
            ]
        );

        // The storage should be empty
        $this->assertFalse($em->contains($entity));
        $this->assertFalse($em->contains($anotherEntity));
        $this->assertNull($repository->find($entity->getId()));
        $this->assertNull($repository->find($anotherEntity->getId()));

        // Try to load 1st entity.
        $load = new DoctrineORMLoader($registry);
        $load(new ContextElement($entity->getId(), $entity));
        $this->assertTrue($em->contains($entity));
        $this->assertNotNull($repository->find($entity->getId()));

        // Try to load 2nd entity
        $load(new ContextElement($anotherEntity->getId(), $anotherEntity));
        $this->assertTrue($em->contains($anotherEntity));
        $this->assertNotNull($repository->find($anotherEntity->getId()));
    }

    public function testLoaderWithBufferedFlush()
    {
        $entity = $this->fakeEntity('foo', 'bar');
        $anotherEntity = $this->fakeEntity('bar', 'baz');
        $className = get_class($entity);

        $registry = $this->fakeManagerRegistry(
            [
                'default' => $em = $this->fakeObjectManager([
                    $className => $repository = $this->fakeRepository($className)
                ])
            ]
        );

        // The storage should be empty
        $this->assertFalse($em->contains($entity));
        $this->assertFalse($em->contains($anotherEntity));
        $this->assertNull($repository->find($entity->getId()));
        $this->assertNull($repository->find($anotherEntity->getId()));

        $eventDispatcher = new ETLEventDispatcher();
        $eventDispatcher->addListener(ETLEvents::AFTER_LOAD, function (ContextElementEvent $event) use ($em, $repository) {
            $loadedEntity = $event->getElement()->getData();
            $this->assertTrue($em->contains($loadedEntity)); // After load, the entity should be present in the unit of work
            $this->assertNull($repository->find($loadedEntity->getId())); // But it should not be flushed yet
        });

        // Init ETL
        $entities   = [
            $entity,
            $anotherEntity,
        ];
        $extract    = new KeyValueExtractor();
        $flushEvery = 2;
        $load       = new DoctrineORMLoader($registry, $flushEvery);
        $run        = new ETLRunner(null, $eventDispatcher);

        $run($entities, $extract, null, $load);
        $this->assertTrue($em->contains($entity));
        $this->assertNotNull($repository->find($entity->getId()));
        $this->assertTrue($em->contains($anotherEntity));
        $this->assertNotNull($repository->find($anotherEntity->getId()));

    }
}
