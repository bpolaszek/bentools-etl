<?php

declare(strict_types=1);

namespace BenTools\ETL\Loader;

use BenTools\ETL\EtlState;
use BenTools\ETL\Exception\LoadException;
use Closure;

use function is_callable;

final readonly class CallableLoader implements LoaderInterface
{
    public function __construct(
        public ?Closure $closure = null,
    ) {
    }

    public function load(mixed $item, EtlState $state): void
    {
        $callback = $state->destination ?? $this->closure;
        if (!is_callable($callback)) {
            throw new LoadException('Invalid destination.');
        }
        $state->context[__CLASS__]['loaded'][] = $callback($item, $state);
    }

    /**
     * @codeCoverageIgnore
     */
    public function flush(bool $isPartial, EtlState $state): mixed
    {
        foreach ($state->context[__CLASS__]['loaded'] ?? [] as $i => $item) {
            $state->context[__CLASS__]['output'][] = $item;
            unset($state->context[__CLASS__]['loaded'][$i]);
        }

        return $state->context[__CLASS__]['output'] ?? [];
    }
}
