This **PHP 7.1+** library provides a very simple implementation of the `Extract / Transform / Load` pattern. 

It is heavily inspired by the [knplabs/etl](https://github.com/docteurklein/php-etl) library, with a more generic approach.

Concept:

* You have an [`iterable`](https://wiki.php.net/rfc/iterable) - i.e an `array`, `\Traversable`, `\Iterator`, `\IteratorAggregate` or a `\Generator` to loop over.
* The `Extractor` is a [`callable`](http://php.net/manual/en/language.types.callable.php) which takes as arguments the key and the value of each element of the loop - its role is to return a new `ContextElement` which contains the extracted data. 
* The `Transformer` is a [`callable`](http://php.net/manual/en/language.types.callable.php) that takes the `ContextElement`'s extracted data, transforms it into the desired output, and hydrates back the `ContextElement`.
* The `Loader` is a [`callable`](http://php.net/manual/en/language.types.callable.php) which takes the `ContextElement` as argument and send the transformed data in a persistence layer, a HTTP Post, a file, ...


The `Runner` class
----------------

The `\BenTools\ETL\Runner\Runner` class is the implementation of the ETL pattern:

```php
$run = new Runner();
$run($iterable, $extractor, $transformer, $loader);
```

You can use an EventDispatcher to skip items or even stop the whole loop.
You can create your own Extractors, Transformers and Loaders by implementing `ExtractorInterface`, `TransformerInterface` and `LoaderInterface` or just use _callables_ that respect the same arguments and return values.


A simple example
---------
Input: **JSON** - Output: **CSV**

```php
use BenTools\ETL\Context\ContextElementInterface;
use BenTools\ETL\Extractor\KeyValueExtractor;
use BenTools\ETL\Loader\CsvFileLoader;
use BenTools\ETL\Runner\Runner;

require_once __DIR__ . '/vendor/autoload.php';

$jsonInput = '{
  "dictators": [
    {
      "country": "USA",
      "name": "Donald Trump"
    },
    {
      "country": "Russia",
      "name": "Vladimir Poutine"
    }
  ]
}';

// We'll iterate over $json
$json = json_decode($jsonInput, true)['dictators'];

// We'll use the default extractor (key => value)
$extractor = new KeyValueExtractor();

// Data transformer
$transformer = function (ContextElementInterface $element) {
    $dictator = $element->getData();
    $element->setData(array_values($dictator));
};

// Init CSV output
$csvOutput = new SplFileObject(__DIR__ . '/output/dictators.csv', 'w');
$csvOutput->fputcsv(['country', 'name']);

// CSV File loader
$loader = new CsvFileLoader($csvOutput);

// Run the ETL
$run = new Runner();
$run($json, $extractor, $transformer, $loader);
```

File contents: 
```csv
country,name
USA,"Donald Trump"
Russia,"Vladimir Poutine"
```

Installation
------------

```
composer require  bentools/etl
```

Tests
------------

```
./vendor/bin/phpunit
```


Advanced usage
--------------

Despite its simple implementation, you can do many things with this library.
You can use a `Logger` to check what's going on and a framework-agnostic `EventDispatcher` (Symfony bridge provided) to hook into the ETL process.
Have a look to our [Recipes](#recipes)

Recipes
-------
[Advanced CSV to JSON conversion](doc/Recipes/AdvancedCSVToJSON.md)

License
-------

MIT
