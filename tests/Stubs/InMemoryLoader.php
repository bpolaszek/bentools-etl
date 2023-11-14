<?php

declare(strict_types=1);

namespace BenTools\ETL\Tests\Stubs;

use BenTools\ETL\EtlState;
use BenTools\ETL\Loader\LoaderInterface;

class InMemoryLoader implements LoaderInterface
{
    public function load(mixed $item, EtlState $state): void
    {
        $state->context['pending'][] = $item;
    }

    /**
     * @return list<list<string>>
     */
    public function flush(bool $isPartial, EtlState $state): array
    {
        $state->context['batchNumber'] ??= 0;
        foreach ($state->context['pending'] as $key => $value) {
            $state->context['batches'][$state->context['batchNumber']][] = $value;
        }
        $state->context['pending'] = [];
        ++$state->context['batchNumber'];

        return $state->context['batches'];
    }
}
