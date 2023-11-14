<?php

declare(strict_types=1);

namespace BenTools\ETL\Loader;

use BenTools\ETL\EtlState;
use BenTools\ETL\Exception\LoadException;

use function fclose;
use function fopen;
use function fwrite;
use function get_debug_type;
use function is_string;
use function sprintf;

use const PHP_EOL;

final readonly class STDOUTLoader implements LoaderInterface
{
    public function __construct(
        private string $eol = PHP_EOL,
    ) {
    }

    public function load(mixed $item, EtlState $state): void
    {
        if (!is_string($item)) {
            throw new LoadException(sprintf('Expected string, got %s.', get_debug_type($item)));
        }

        $state->context[__CLASS__]['pending'][] = $item;
    }

    public function flush(bool $isPartial, EtlState $state): int
    {
        $pendingItems = $state->context[__CLASS__]['pending'] ?? [];
        $state->context[__CLASS__]['resource'] ??= fopen('php://stdout', 'wb+');
        $state->context[__CLASS__]['nbWrittenBytes'] ??= 0;
        foreach ($pendingItems as $item) {
            $state->context[__CLASS__]['nbWrittenBytes'] += fwrite(
                $state->context[__CLASS__]['resource'],
                $item.$this->eol,
            );
        }

        $nbWrittenBytes = $state->context[__CLASS__]['nbWrittenBytes'];
        if (!$isPartial) {
            // fclose($state->context[__CLASS__]['resource']);
            unset($state->context[__CLASS__]);
        }

        return $nbWrittenBytes;
    }
}
