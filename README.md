bentools/etl
------------

This 7.1+ library provides a very simple implementation of the `Extract / Transform / Load` pattern. 

It is heavily inspired by the [knplabs/etl](https://github.com/docteurklein/php-etl) library, with a more generic approach.

Concept:

* An `Extractor` is an [`iterable`](https://wiki.php.net/rfc/iterable) - i.e an `array`, `\Traversable`, `\Iterator`, `IteratorAggregate` or `\Generator`
* A `Transformer` is a `callable` responsible to transform each item returned by the `Extractor` to the desired output
* A `Loader` is a `callable` responsible to send the transformed data in a persistence layer, a HTTP Post, a file, ...

Each item should be stored in a `BenTools\ETL\Context\ContextElement` object. This object handles:

* The item identifier
* The original (_extracted_) data
* The transformed data

The `Runner` class
----------------

The `\BenTools\ETL\Runner\Runner` class is the implementation of the pattern:

* For each item of the `extractor`
* It calls the `transformer` to transform it
* Then it calls the `loader`


A simple example
---------
i.e. Transforming objects to arrays and store them in a variable.

```php
$dictators = [];

$extractor = function () {

    $trump = new stdClass();
    $trump->name = 'Donald Trump';

    $poutine = new stdClass();
    $poutine->name = 'Vladimir Poutine';

    yield 'usa' => $trump;
    yield 'russia' => $poutine;
};

$transformer = function (ContextElementInterface $element) {
    $dictator = $element->getExtractedData();
    $element->setTransformedData((array) $dictator);
};

$loader = function (ContextElementInterface $element) use (&$dictators) {
    $id = $element->getIdentifier();
    $dictators[$id] = $element->getTransformedData();
};

$run = new Runner();
$run($extractor(), $transformer, $loader);

var_dump($dictators);
```

Outputs: 
```php
array(2) {
  ["usa"]=>
  array(1) {
    ["name"]=>
    string(12) "Donald Trump"
  }
  ["russia"]=>
  array(1) {
    ["name"]=>
    string(16) "Vladimir Poutine"
  }
}
```

Installation
------------

```
composer require  bentools/etl
```

Advanced usage
--------------

This is not documented yet but if you look further, you'll know how to skip items, and abort an ETL operation.
You can also use a `Logger` to check what's going on and a framework-agnostic `EventDispatcher` (Symfony bridge provided)  to hook into the ETL process.

License
-------

MIT
