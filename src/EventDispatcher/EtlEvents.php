<?php

namespace BenTools\ETL\EventDispatcher;

final class EtlEvents
{
    /**
     * Fired at the very beginning of the process.
     * This event will yield a \BenTools\ETL\EventDispatcher\Event\StartProcessEvent object.
     */
    public const START = 'start';

    /**
     * Fired after an item has been extracted.
     * This event will yield a \BenTools\ETL\EventDispatcher\Event\ItemEvent object.
     */
    public const EXTRACT = 'extract';

    /**
     * Fired when extracting an item resulted in an exception.
     * This event will yield a \BenTools\ETL\EventDispatcher\Event\ItemExceptionEvent object.
     */
    public const EXTRACT_EXCEPTION = 'extract.exception';

    /**
     * Fired after an item has been transformed.
     * This event will yield a \BenTools\ETL\EventDispatcher\Event\ItemEvent object.
     */
    public const TRANSFORM = 'transform';

    /**
     * Fired when transforming an item resulted in an exception.
     * This event will yield a \BenTools\ETL\EventDispatcher\Event\ItemExceptionEvent object.
     */
    public const TRANSFORM_EXCEPTION = 'transform.exception';

    /**
     * This event is fired when initializing the loader (just before the 1st item gets loaded).
     * This event will yield a \BenTools\ETL\EventDispatcher\Event\ItemEvent object.
     */
    public const LOADER_INIT = 'loader.init';

    /**
     * Fired after an item has been loaded.
     * This event will yield a \BenTools\ETL\EventDispatcher\Event\ItemEvent object.
     */
    public const LOAD = 'load';

    /**
     * Fired when loading an item resulted in an exception.
     * This event will yield a \BenTools\ETL\EventDispatcher\Event\ItemExceptionEvent object.
     */
    public const LOAD_EXCEPTION = 'load.exception';

    /**
     * Fired after an item has been skipped.
     * This event will yield a \BenTools\ETL\EventDispatcher\Event\ItemEvent object.
     */
    public const SKIP = 'skip';

    /**
     * Fired after an item required the ETL to stop.
     * This event will yield a \BenTools\ETL\EventDispatcher\Event\ItemEvent object.
     */
    public const STOP = 'stop';

    /**
     * Fired after a flush operation has been completed.
     * This event will yield a \BenTools\ETL\EventDispatcher\Event\FlushEvent object.
     */
    public const FLUSH = 'flush';

    /**
     * Fired after a rollback operation has been completed.
     * This event will yield a \BenTools\ETL\EventDispatcher\Event\RollbackEvent object.
     */
    public const ROLLBACK = 'rollback';

    /**
     * Fired at the end of the ETL process.
     * This event will yield a \BenTools\ETL\EventDispatcher\Event\EndProcessEvent object.
     */
    public const END = 'end';
}
