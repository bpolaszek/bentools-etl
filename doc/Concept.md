Concept
=======
The idea behind the ETL pattern is to loop over an [`iterable`](https://wiki.php.net/rfc/iterable) to transfer each key / value pair through 3 tasks:

Extract
-------
The `Extractor` is a [`callable`](http://php.net/manual/en/language.types.callable.php) factory which is responsible to return a new `BenTools\ETL\Context\ContextElementInterface` object which contains the extracted data. 

Transform
---------
The `Transformer` is a [`callable`](http://php.net/manual/en/language.types.callable.php) that takes the `ContextElement`'s extracted data, transforms it into the desired output, can even change the key, and hydrates back the `ContextElement`.

Load
----
The `Loader` is a [`callable`](http://php.net/manual/en/language.types.callable.php) which takes the `ContextElement` as argument and send the transformed data in a persistence layer, a HTTP Post, a file, ...




Implementation
==============

All you have to do is to implement `BenTools\ETL\Extractor\ExtractorInterface`, `BenTools\ETL\Transformer\TransformerInterface` and `BenTools\ETL\Loader\LoaderInterface` or use already provided classes.

The only method to implement is `__invoke()`. Thus, feel free to use simple _callables_ that respect the same arguments and return values.

You then need an [`iterable`](https://wiki.php.net/rfc/iterable) - i.e an `array`, `\Traversable`, `\Iterator`, `\IteratorAggregate` or a `\Generator` to loop over.


Usage
-----

The `BenTools\ETL\Runner\ETLRunner` class is the implementation of the ETL pattern.

Here's the contract:

```php
namespace BenTools\ETL\Runner;

use BenTools\ETL\Extractor\ExtractorInterface;
use BenTools\ETL\Transformer\TransformerInterface;

interface ETLRunnerInterface
{

    /**
     * @param iterable|\Generator|\Traversable|array $items
     * @param callable|ExtractorInterface            $extractor
     * @param callable|TransformerInterface          $transformer
     * @param callable                               $loader
     */
    public function __invoke(iterable $items, callable $extractor, callable $transformer = null, callable $loader);
}
```

How to use it:
```php
use BenTools\ETL\Runner\ETLRunner;
$run = new ETLRunner();
$run($iterable, $extractor, $transformer, $loader);
```

When invoked, the runner will loop over `$iterable`, then call the `$extractor`, the `$transformer` and the `$loader` consecutively.

As you can notice, the transformer is optionnal, meaning the extracted data can be directly loaded if no transformation is needed.


Advanced Usage
--------------
The `BenTools\ETL\Runner\ETLRunner` constructor accepts 2 optionnal arguments:

* A `Psr\Log\LoggerInterface` logger like Monolog to get some info about the ETL process
* A `BenTools\ETL\Event\EventDispatcher\EventDispatcherInterface` event manager of your own (or use the Symfony Bridge provided) to hook on the ETL process (see [Events](Events.md)).


Next: [Getting started](GettingStarted.md)