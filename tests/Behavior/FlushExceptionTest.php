<?php

declare(strict_types=1);

namespace BenTools\ETL\Tests\Behavior;

use BenTools\ETL\EtlConfiguration;
use BenTools\ETL\EtlExecutor;
use BenTools\ETL\EtlState;
use BenTools\ETL\Exception\FlushException;
use BenTools\ETL\Loader\LoaderInterface;
use Exception;
use RuntimeException;

use function it;

it('throws a load exception when it is thrown from the extractor', function () {
    // Given
    $loader = new FlushFailsLoader(new FlushException('Flush failed.'));
    $etl = (new EtlExecutor(loader: $loader, options: new EtlConfiguration(flushEvery: 2)));

    // When
    $etl->process(['banana', 'apple', 'strawberry', 'raspberry', 'peach']);
})->throws(FlushException::class, 'Flush failed.');

it('throws a load exception when some other exception is thrown', function () {
    // Given
    $loader = new FlushFailsLoader(new RuntimeException('Flush failed.'));
    $etl = (new EtlExecutor(loader: $loader, options: new EtlConfiguration(flushEvery: 2)));

    // When
    $etl->process(['banana', 'apple', 'strawberry', 'raspberry', 'peach']);
})->throws(FlushException::class, 'Error during flush.');

class FlushFailsLoader implements LoaderInterface
{
    public function __construct(
        private Exception $failure,
    ) {
    }

    public function load(mixed $item, EtlState $state): void
    {
    }

    public function flush(bool $isPartial, EtlState $state): never
    {
        throw $this->failure;
    }
}
