Concept
=======
The idea behind the ETL pattern is to transfer an object through 3 tasks:

Extract
-------
The `Extractor` is a [`callable`](http://php.net/manual/en/language.types.callable.php) which takes as arguments the key and the value of each element of the loop - its role is to return a new `BenTools\ETL\Context\ContextElementInterface` object which contains the extracted data. 

Transform
---------
The `Transformer` is a [`callable`](http://php.net/manual/en/language.types.callable.php) that takes the `ContextElement`'s extracted data, transforms it into the desired output, and hydrates back the `ContextElement`.

Load
----
The `Loader` is a [`callable`](http://php.net/manual/en/language.types.callable.php) which takes the `ContextElement` as argument and send the transformed data in a persistence layer, a HTTP Post, a file, ...




Implementation
==============

All you have to do is to implement `BenTools\ETL\Extractor\ExtractorInterface`, `BenTools\ETL\Transformer\TransformerInterface` and `BenTools\ETL\Loader\LoaderInterface` or use already provided classes.

The only method to implement is `__invoke()`.

Because of the very simplicity of each task, feel free to use simple _callables_ that respect the same arguments and return values.

You then need an [`iterable`](https://wiki.php.net/rfc/iterable) - i.e an `array`, `\Traversable`, `\Iterator`, `\IteratorAggregate` or a `\Generator` to loop over.


The `Runner` class
----------------

The `BenTools\ETL\Runner\Runner` class is the implementation of the ETL pattern:

```php
use BenTools\ETL\Runner\Runner;
$run = new Runner();
$run($iterable, $extractor, $transformer, $loader);
```

When invoked, the runner will loop over `$iterable`, then call the `$extractor`, the `$transformer` and the `$loader` consecutively.

You can use an EventDispatcher to skip items or even stop the whole loop.
You can create your own Extractors, Transformers and Loaders by implementing `ExtractorInterface`, `TransformerInterface` and `LoaderInterface` or just use _callables_ that respect the same arguments and return values.


Next: [Getting started](GettingStarted.md)