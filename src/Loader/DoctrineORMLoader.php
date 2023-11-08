<?php

declare(strict_types=1);

namespace BenTools\ETL\Loader;

use BenTools\ETL\EtlState;
use BenTools\ETL\Exception\LoadException;
use Doctrine\Persistence\ManagerRegistry;
use SplObjectStorage;

use function gettype;
use function is_object;
use function sprintf;

final readonly class DoctrineORMLoader implements LoaderInterface
{
    public function __construct(
        private ManagerRegistry $managerRegistry,
    ) {
    }

    public function load(mixed $item, EtlState $state): void
    {
        if (!is_object($item)) {
            throw new LoadException(sprintf('Expecting object, got %s.', gettype($item)));
        }

        $manager = $this->managerRegistry->getManagerForClass($item::class)
            ?? throw new LoadException(sprintf('Could not find manager for class %s.', $item::class));

        $managers = $state->context[__CLASS__]['managers'] ??= new SplObjectStorage();
        $managers->attach($manager);
        $manager->persist($item);
    }

    public function flush(bool $isPartial, EtlState $state): null
    {
        $managers = $state->context[__CLASS__]['managers'] ??= new SplObjectStorage();
        foreach ($managers as $manager) {
            $manager->flush();
            $managers->detach($manager);
        }

        return null;
    }
}
