<?php

declare(strict_types=1);

namespace BenTools\ETL\Tests\Unit\Loader;

use Bentools\ETL\EtlConfiguration;
use Bentools\ETL\EtlExecutor;
use Bentools\ETL\EtlState;
use Bentools\ETL\Exception\LoadException;
use Bentools\ETL\Loader\DoctrineORMLoader;
use BenTools\ETL\Tests\Unit\Loader\Doctrine\Book;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Mockery;
use stdClass;

it('works', function () {
    $registry = Mockery::mock(ManagerRegistry::class);
    $manager = Mockery::mock(ObjectManager::class);
    $registry->shouldReceive('getManagerForClass')->andReturn($manager);
    $manager->shouldReceive('persist')->twice();
    $manager->shouldReceive('flush')->once();

    $executor = (new EtlExecutor(options: new EtlConfiguration(flushEvery: 10)))
        ->transformWith(function (array $book) {
            yield new Book($book['id'], $book['name']);
        })
        ->loadInto(new DoctrineORMLoader($registry));
    $executor->process([
        ['id' => 1, 'name' => 'Holy Bible'],
        ['id' => 2, 'name' => 'Fifty Shades of Grey'],
    ]);
});

it('complains if loaded item is not an object', function () {
    $loader = new DoctrineORMLoader(Mockery::mock(ManagerRegistry::class));
    $loader->load([], new EtlState());
})->throws(LoadException::class, 'Expecting object, got array.');

it('complains if loaded item is not a mapped Doctrine class', function () {
    $registry = Mockery::mock(ManagerRegistry::class);
    $registry->shouldReceive('getManagerForClass')->andReturn(null);
    $loader = new DoctrineORMLoader($registry);
    $loader->load(new stdClass(), new EtlState());
})->throws(LoadException::class, 'Could not find manager for class stdClass.');
