<?php

declare(strict_types=1);

namespace Bentools\ETL\Loader;

use Bentools\ETL\EtlState;

use function array_merge;

final readonly class InMemoryLoader implements LoaderInterface
{
    public function load(mixed $item, EtlState $state): void
    {
        $state->context['pending'][] = $item;
    }

    /**
     * @return list<list<mixed>>
     */
    public function flush(bool $isPartial, EtlState $state): array
    {
        $state->context['batchNumber'] ??= 0;
        foreach ($state->context['pending'] as $key => $value) {
            $state->context['batches'][$state->context['batchNumber']][] = $value;
        }
        $state->context['pending'] = [];
        ++$state->context['batchNumber'];

        return array_merge(...$state->context['batches'] ?? []);
    }
}
