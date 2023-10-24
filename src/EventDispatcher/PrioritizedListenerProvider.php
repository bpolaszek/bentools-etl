<?php

declare(strict_types=1);

namespace Bentools\ETL\EventDispatcher;

use Psr\EventDispatcher\ListenerProviderInterface;

use function array_merge;
use function krsort;

final class PrioritizedListenerProvider implements ListenerProviderInterface
{
    /**
     * @var array<class-string, array<int, array<callable>>>
     */
    private array $prioritizedListeners = [];

    /**
     * @var array<class-string, array<callable>>
     */
    private array $flattenedListeners = [];

    public function listenTo(string $eventClass, callable $callback, int $priority = 0): void
    {
        $this->prioritizedListeners[$eventClass][$priority][] = $callback;
        krsort($this->prioritizedListeners[$eventClass]);
        $this->flattenedListeners[$eventClass] = array_merge(...$this->prioritizedListeners[$eventClass]);
    }

    /**
     * @return iterable<callable>
     */
    public function getListenersForEvent(object $event): iterable
    {
        return $this->flattenedListeners[$event::class] ?? [];
    }
}
