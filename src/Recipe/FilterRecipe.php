<?php

declare(strict_types=1);

namespace BenTools\ETL\Recipe;

use BenTools\ETL\EtlExecutor;
use BenTools\ETL\EventDispatcher\Event\BeforeLoadEvent;
use BenTools\ETL\EventDispatcher\Event\ExtractEvent;
use Closure;
use InvalidArgumentException;

use function in_array;
use function sprintf;

final class FilterRecipe extends Recipe
{
    private const EVENTS_CLASSES = [ExtractEvent::class, BeforeLoadEvent::class];

    public function __construct(
        private readonly Closure $filter,
        private readonly string $eventClass = ExtractEvent::class,
        private readonly int $priority = 0,
        private readonly FilterRecipeMode $mode = FilterRecipeMode::INCLUDE,
    ) {
        if (!in_array($this->eventClass, self::EVENTS_CLASSES)) {
            throw new InvalidArgumentException(sprintf('Can only filter on ExtractEvent / LoadEvent, not %s', $this->eventClass));
        }
    }

    public function decorate(EtlExecutor $executor): EtlExecutor
    {
        return match ($this->eventClass) {
            ExtractEvent::class => $executor->onExtract($this(...), $this->priority),
            BeforeLoadEvent::class => $executor->onBeforeLoad($this(...), $this->priority),
            default => $executor,
        };
    }

    public function __invoke(ExtractEvent|BeforeLoadEvent $event): void
    {
        $matchFilter = !($this->filter)($event->item, $event->state);
        if (FilterRecipeMode::EXCLUDE === $this->mode) {
            $matchFilter = !$matchFilter;
        }

        if ($matchFilter) {
            $event->state->skip();
        }
    }
}
