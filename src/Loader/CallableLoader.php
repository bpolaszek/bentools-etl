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
        $state->context['output'] = $callback($item, $state);
        $state->flush();
    }

    /**
     * @codeCoverageIgnore
     */
    public function flush(bool $isPartial, EtlState $state): mixed
    {
        return $state->context['output'];
    }
}
