<?php

declare(strict_types=1);

namespace BenTools\ETL\Tests\Behavior;

use Bentools\ETL\EtlConfiguration;
use Bentools\ETL\EtlExecutor;
use Bentools\ETL\EtlState;
use Bentools\ETL\Exception\FlushException;
use Bentools\ETL\Loader\LoaderInterface;
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
