Event Dispatcher
================

The library ships with a built-in Event dispatcher that allow you to hook at different points within the ETL process.

If you're running Symfony, you can use Symfony's Event Dispatcher by wrapping it within into the `BenTools\ETL\Event\EventDispatcher\Bridge\Symfony\SymfonyEventDispatcherBridge` class.

You're also free to create your own bridge if you're using another framework, just implement `BenTools\ETL\Event\EventDispatcher\EventDispatcherInterface`.

ETL Events
==========

These events (see `BenTools\ETL\Event\ETLEvents`) are fired by `BenTools\ETL\Runner\ETLRunner` during the loop.

ETLEvents::START
-----------------------------------
This event is fired just before beginning iterating.

ETLEvents::AFTER_EXTRACT
-------------------------------------------
This event is fired after an item's extraction. You get a fresh `BenTools\ETL\Context\ContextElement` object.

ETLEvents::AFTER_TRANSFORM
-------------------------------------------
This event is fired once the item is transformed. You get the `BenTools\ETL\Context\ContextElement` object with the transformed data.

ETLEvents::AFTER_LOAD
-------------------------------------------
This event is fired on load. You have access to the `BenTools\ETL\Context\ContextElement` object.

**Note**: For loaders that implement `BenTools\ETL\Loader\FlushableLoaderInterface`, like `BenTools\ETL\Loader\DoctrineORMLoader` a loaded object does not necessarily mean it is already commited to the persistence layer.

ETLEvents::AFTER_FLUSH
-------------------------------------------
This event is fired when a `BenTools\ETL\Loader\FlushableLoaderInterface` loader flushes waiting objects.

You can't get a `BenTools\ETL\Context\ContextElement` object since it is a global event.


ETLEvents::END
-----------------------------------
This event is fired when the process is finished.


Next: [Recipes](Recipes/AdvancedCSVToJSON.md)