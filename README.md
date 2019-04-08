[![Latest Stable Version](https://poser.pugx.org/bentools/etl/v/stable)](https://packagist.org/packages/bentools/etl)
[![License](https://poser.pugx.org/bentools/etl/license)](https://packagist.org/packages/bentools/etl)
[![Build Status](https://img.shields.io/travis/bpolaszek/bentools-etl/master.svg?style=flat-square)](https://travis-ci.org/bpolaszek/bentools-etl)
[![Coverage Status](https://coveralls.io/repos/github/bpolaszek/bentools-etl/badge.svg?branch=master)](https://coveralls.io/github/bpolaszek/bentools-etl?branch=master)
[![Quality Score](https://img.shields.io/scrutinizer/g/bpolaszek/bentools-etl.svg?style=flat-square)](https://scrutinizer-ci.com/g/bpolaszek/bentools-etl)
[![Total Downloads](https://poser.pugx.org/bentools/etl/downloads)](https://packagist.org/packages/bentools/etl)

Okay, so you heard about the [Extract / Transform / Load](https://en.wikipedia.org/wiki/Extract,_transform,_load) pattern and you're looking for a PHP library to do the stuff.

Alright, let's go! 

Installation
------------

```bash
composer require bentools/etl:^3.0@alpha
```

_Warning: version 3.0 is a complete rewrite and a involves important BC breaks. Don't upgrade from `^2.0` unless you know what you're doing!_

Usage
-----

To sum up, you will apply _transformations_ onto an `iterable` of any _things_ in order to _load_ them in some place. 
Sometimes your `iterable` is ready to go, sometimes you just don't need to perform transformations, but anyway you need to load that data somewhere.

Let's start with a really simple example:

```php
use BenTools\ETL\EtlBuilder;
use BenTools\ETL\Loader\JsonFileLoader;

$data = [
    'foo',
    'bar',
];

$etl = EtlBuilder::init()
    ->loadInto(JsonFileLoader::toFile(__DIR__.'/data.json'))
    ->createEtl();
$etl->process($data);
```

Basically you just loaded the string `["foo","bar"]` into `data.json`. Yay!

Now let's apply a basic uppercase transformation:

```php
use BenTools\ETL\EtlBuilder;
use BenTools\ETL\Loader\JsonFileLoader;

$data = [
    'foo',
    'bar',
];

$etl = EtlBuilder::init()
    ->transformWith(new CallableTransformer('strtoupper'))
    ->loadInto(JsonFileLoader::factory())
    ->createEtl();
$etl->process($data, __DIR__.'/data.json'); // You can also set the output file when processing :-)
```

Didn't you just write the string `["FOO","BAR"]` into `data.json` ? Yes, you did!

Okay, but what if your source data is not an iterable (yet)? It can be a CSV file or a CSV string, for instance. Here's another example:

```php
use BenTools\ETL\EtlBuilder;
use BenTools\ETL\Extractor\CsvExtractor;
use BenTools\ETL\Loader\JsonFileLoader;

$data = <<<CSV
country_code,country_name,president
US,USA,"Donald Trump"
RU,Russia,"Vladimir Putin"
CSV;

$etl = EtlBuilder::init()
    ->extractFrom(new CsvExtractor())
    ->loadInto(JsonFileLoader::factory(['json_options' => \JSON_PRETTY_PRINT]))
    ->createEtl();
$etl->process($data, __DIR__.'/data.json');
```

As you guessed, the following content was just written into `presidents.json`:

```json
[
    {
        "country_code": "US",
        "country_name": "USA",
        "president": "Donald Trump"
    },
    {
        "country_code": "RU",
        "country_name": "Russia",
        "president": "Vladimir Putin"
    }
]
```

We provide helpful extractors and loaders to manipulate JSON, CSV, text, and you'll also find a `DoctrineORMLoader` for when your transformer _yields_ Doctrine entities.

Because yes, a transformer must return a `\Generator`. Why? Because a single extracted item can lead to several output items. Let's take a more sophisticated example:

```php
use BenTools\ETL\EtlBuilder;
use BenTools\ETL\Extractor\JsonExtractor;

$pdo = new \PDO('mysql:host=localhost;dbname=test');
$input = __DIR__.'/presidents.json';

$etl = EtlBuilder::init()
    ->extractFrom(new JsonExtractor())
    ->transformWith(
        function ($item) use ($pdo) {
            $stmt = $pdo->prepare('SELECT country_code FROM countries WHERE country_code = ?');
            $stmt->bindValue(1, $item['country_code'], \PDO::PARAM_STR);
            $stmt->execute();
            if (0 === $stmt->rowCount()) {
                yield ['INSERT INTO countries (country_code, country_name) VALUES (?, ?)', [$item['country_code'], $item['country_name']]];
            }

            yield ['REPLACE INTO presidents (country_code, president_name) VALUES (?, ?)', [$item['country_code'], $item['president']]];

        }
    )
    ->loadInto(
        $loader = function (\Generator $queries) use ($pdo) {
            foreach ($queries as $query) {
                list($sql, $params) = $query;
                $stmt = $pdo->prepare($sql);
                foreach ($params as $i => $value) {
                    $stmt->bindValue($i + 1, $value);
                }
                $stmt->execute();
            }
        }
    )
    ->createEtl();

$etl->process(__DIR__.'/presidents.json'); // The JsonExtractor will convert that file to a PHP array
```

As you can see, from a single item, we loaded up to 2 queries.

Your _extractors_, _transformers_ and _loaders_ can implement [`ExtractorInterface`](https://github.com/bpolaszek/bentools-etl/tree/master/src/Extractor/ExtractorInterface.php), [`TransformerInterface`](https://github.com/bpolaszek/bentools-etl/tree/master/src/Transformer/TransformerInterface.php) or [`LoaderInterface`](https://github.com/bpolaszek/bentools-etl/tree/master/src/Loader/LoaderInterface.php) as well as being simple `callables`.


Skipping items
--------------

Each _extractor_ / _transformer_ / _loader_ callback gets the current `Etl` object injected in their arguments. 

This allows you to ask the ETL to skip an item, or even to stop the whole process:

```php
use BenTools\ETL\Etl;
use BenTools\ETL\EtlBuilder;
use BenTools\ETL\Transformer\CallableTransformer;

$fruits = [
    'apple',
    'banana',
    'strawberry',
    'pineapple',
    'pear',
];


$storage = [];
$etl = EtlBuilder::init()
    ->transformWith(new CallableTransformer('strtoupper'))
    ->loadInto(
        function ($generated, $key, Etl $etl) use (&$storage) {
            foreach ($generated as $fruit) {
                if ('BANANA' === $fruit) {
                    $etl->skipCurrentItem();
                    break;
                }
                if ('PINEAPPLE' === $fruit) {
                    $etl->stopProcessing();
                    break;
                }
                $storage[] = $fruit;
            }
        })
    ->createEtl();

$etl->process($fruits);

var_dump($storage); // ['APPLE', 'STRAWBERRY']
```


Events
------

Now you're wondering how you can hook on the ETL lifecycle, to log things, handle exceptions, ... This library ships with a built-in Event Dispatcher that you can leverage when:

* The ETL starts
* An item has been extracted
* The extraction failed
* An item has been transformed
* Transformation failed
* Loader is initialized (1st item is about to be loaded)
* An item has been loaded
* Loading failed
* An item has been skipped
* The ETL was stopped
* A flush operation was completed
* A rollback operation was completed
* The ETL completed the whole process.

The _ItemEvents_ (on extract, transform, load) will allow you to mark the current item to be skipped, or even handle runtime exceptions. Let's take another example:

```php
use BenTools\ETL\EtlBuilder;
use BenTools\ETL\EventDispatcher\Event\ItemExceptionEvent;

$fruits = [
    'apple',
    new \RuntimeException('Is tomato a fruit?'),
    'banana',
];


$storage = [];
$etl = EtlBuilder::init()
    ->transformWith(
        function ($item, $key) {
            if ($item instanceof \Exception) {
                throw $item;
            }

            yield $key => $item;
        })
    ->loadInto(
        function (iterable $transformed) use (&$storage) {
            foreach ($transformed as $fruit) {
                $storage[] = $fruit;
            }
        })
    ->onTransformException(
        function (ItemExceptionEvent $event) {
            echo $event->getException()->getMessage(); // Is tomato a fruit?
            $event->ignoreException();
        })
    ->createEtl();

$etl->process($fruits);

var_dump($storage); // ['apple', 'banana']
```

Here, we intentionnally threw an exception during the _transform_ operation. But thanks to the event dispatcher, we could tell the ETL this exception can be safely ignored and it can pursue the rest of the process.

You can attach as many event listeners as you wish, and sort them by priority.


Recipes
-------

A recipe is an ETL pattern that can be reused through different tasks.
If you want to log everything that goes through an ETL for example, use our built-in Logger recipe:

```php
use BenTools\ETL\EtlBuilder;
use BenTools\ETL\Recipe\LoggerRecipe;

$etl = EtlBuilder::init()
    ->useRecipe(new LoggerRecipe($logger))
    ->createEtl();
```

You can also create your own recipes:

```php
use BenTools\ETL\EtlBuilder;
use BenTools\ETL\EventDispatcher\Event\ItemEvent;
use BenTools\ETL\Extractor\JsonExtractor;
use BenTools\ETL\Loader\CsvFileLoader;
use BenTools\ETL\Recipe\Recipe;


class JSONToCSVRecipe extends Recipe
{
    /**
     * @inheritDoc
     */
    public function updateBuilder(EtlBuilder $builder): EtlBuilder
    {
        return $builder
            ->extractFrom(new JsonExtractor())
            ->loadInto($loader = CsvFileLoader::factory(['delimiter' => ';']))
            ->onLoaderInit(
                function (ItemEvent $event) use ($loader) {
                    $loader::factory(['keys' => array_keys($event->getItem())], $loader);
                })
            ;
    }

}

$builder = EtlBuilder::init()->useRecipe(new JSONToCSVRecipe());
$etl = $builder->createEtl();
$etl->process(__DIR__.'/presidents.json', __DIR__.'/presidents.csv');
```

The above example will result in `presidents.csv` containing:
```csv
country_code;country_name;president
US;USA;"Donald Trump"
RU;Russia;"Vladimir Putin"
```

To sum up, a _recipe_ is a kind of an `ETLBuilder` factory, but keep in mind that a recipe will only **add** event listeners to the existing builder but can also **replace** the builder's _extractor_, _transformer_ and/or _loader_.

Tests
-----

```bash
./vendor/bin/phpunit
```

License
-------

MIT
