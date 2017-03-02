ETL Events
==========

You can hook anywhere into the ETL process by using your own Event Dispatcher 
(just implement the `BenTools\ETL\Event\EventDispatcher\EventDispatcherInterface`) or by using Symfony's Event dispatcher thanks o the Bridge provided.

BenTools\ETL\Event\ETLEvents::START
-----------------------------------
This event is fired just before beginning iterating.

BenTools\ETL\Event\ETLEvents::AFTER_EXTRACT
-------------------------------------------
This event is fired after an item's extraction. You get a fresh `BenTools\ETL\Context\ContextElement` object.

BenTools\ETL\Event\ETLEvents::AFTER_TRANSFORM
-------------------------------------------
This event is fired once the item is transformed. You get the `BenTools\ETL\Context\ContextElement` object with the transformed data.

BenTools\ETL\Event\ETLEvents::AFTER_LOAD
-------------------------------------------
This event is fired on load. You have access to the `BenTools\ETL\Context\ContextElement` object.

**Note**: For loaders that implement `BenTools\ETL\Loader\FlushableLoaderInterface`, like `BenTools\ETL\Loader\DoctrineORMLoader` a loaded object does not necessarily mean it is already commited to the persistence layer.

BenTools\ETL\Event\ETLEvents::AFTER_FLUSH
-------------------------------------------
This event is fired when a `BenTools\ETL\Loader\FlushableLoaderInterface` loader flushes waiting objects.

You can't get a `BenTools\ETL\Context\ContextElement` object since it is a global event.


BenTools\ETL\Event\ETLEvents::END
-----------------------------------
This event is fired when the process is finished.


Next: [Recipes](Recipes/AdvancedCSVToJSON.md)