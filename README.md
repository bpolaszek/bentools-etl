[![Latest Unstable Version](http://poser.pugx.org/bentools/etl/v/unstable)](https://packagist.org/packages/bentools/etl)
[![Latest Stable Version](https://poser.pugx.org/bentools/etl/v/stable)](https://packagist.org/packages/bentools/etl)
[![License](https://poser.pugx.org/bentools/etl/license)](https://packagist.org/packages/bentools/etl)
[![CI Workflow](https://github.com/bpolaszek/bentools-etl/actions/workflows/ci.yml/badge.svg)](https://github.com/bpolaszek/bentools-etl/actions/workflows/ci.yml)
[![Coverage](https://codecov.io/gh/bpolaszek/bentools-etl/branch/master/graph/badge.svg?token=L5ulTaymbt)](https://codecov.io/gh/bpolaszek/bentools-etl)
[![Total Downloads](https://poser.pugx.org/bentools/etl/downloads)](https://packagist.org/packages/bentools/etl)

Okay, so you heard about the [Extract / Transform / Load](https://en.wikipedia.org/wiki/Extract,_transform,_load) pattern,
and you're looking for a PHP library to do the stuff. Alright, let's go!

`bentools/etl` is a versatile PHP library for implementing the Extract, Transform, Load (ETL) pattern, designed to streamline data processing tasks.

Table of Contents
-----------------

- [Concepts](#concepts)
- [Installation](#installation)
- [Getting started](#usage)
  - [The EtlState object](doc/getting-started.md#the-etlstate-object)
  - [Skipping items](doc/getting-started.md#skipping-items)
  - [Stopping the workflow](doc/getting-started.md#stopping-the-workflow)
  - [Using events](doc/getting-started.md#using-events)
  - [Flush frequency and early flushes](doc/getting-started.md#flush-frequency-and-early-flushes)
- [Advanced Usage](doc/advanced_usage.md)
    - [Creating your own Extractor / Transformers / Loaders](doc/advanced_usage.md#creating-your-own-extractor--transformers--loaders)
    - [Difference between yield and return in transformers](doc/advanced_usage.md#difference-between-yield-and-return-in-transformers)
    - [Next tick](doc/advanced_usage.md#next-tick)
    - [Chaining extractors / transformers / loaders](doc/advanced_usage.md#chaining-extractors--transformers--loaders)
    - [Instantiators](doc/advanced_usage.md#instantiators)
- [Recipes](doc/recipes.md)
- [Contributing](#contribute)
- [License](#license)

Concepts
--------

Let's cover the basic concepts:
- **Extract**: you have a source of data (a database, a CSV file, whatever) - an **extractor** is able to read that data and provide an iterator of items
- **Transform**: apply transformation to each item. A **transformer** may generate 0, 1 or several items to **load** (for example, 1 item may generate multiple SQL queries)
- **Load**: load transformed item to the destination. For example, **extracted items** have been **transformed** to SQL queries, and your **loader** will run those queries against your database.

Installation
------------

```bash
composer require bentools/etl:^4.0@alpha
```

> **Warning #1**: Version 4.0 is a complete rewrite and introduces significant BC (backward compatibility) breaks.
> Avoid upgrading from `^2.0` or `^3.0` unless you're fully aware of the changes.

> **Warning #2**: Version 4.0 is still at an alpha stage. BC breaks might occur between alpha releases.

Usage
-----

Now let's have a look on how simple it is:

```php
use BenTools\ETL\EtlExecutor;

// Given
$singers = ['Bob Marley', 'Amy Winehouse'];

// Transform each singer's name to uppercase and process the array
$etl = (new EtlExecutor())
    ->transformWith(fn (string $name) => strtoupper($name));

// When
$report = $etl->process($singers);

// Then
var_dump($report->output); // ["BOB MARLEY", "AMY WINEHOUSE"]
```

OK, that wasn't really hard, here we basically don't have to _extract_ anything (we can already iterate on `$singers`),
and we're not _loading_ anywhere, except into PHP's memory. 

You may ask, "why don't you just `array_map('strtoupper', $singers)` ?" and you're totally right.

But sometimes, extracting, transforming and / or loading get a little more complex. 
You may want to extract from a file, a crawled content on the web, perform one to many transformations, maybe skip some items,
or reuse some extraction, transformation or loading logic.

Here's another example of what you can do:

```php
use BenTools\ETL\EventDispatcher\Event\TransformEvent;
use BenTools\ETL\Loader\JSONLoader;

use function BenTools\ETL\extractFrom;

$executor = extractFrom(function () {
    yield ['firstName' => 'Barack', 'lastName' => 'Obama'];
    yield ['firstName' => 'Donald', 'lastName' => 'Trump'];
    yield ['firstName' => 'Joe', 'lastName' => 'Biden'];
})
    ->transformWith(fn (array $item) => implode(' ', array_values($item)))
    ->loadInto(new JSONLoader())
    ->onTransform(function (TransformEvent $event) {
        if ('Donald Trump' === $event->transformResult->value) {
            $event->state->skip();
        }
    });

$report = $executor->process();

dump($report->output); // string '["Barack Obama", "Joe Biden"]'
```

Or: 

```php
$report = $executor->process(destination: 'file:///tmp/presidents.json');
var_dump($report->output); // string 'file:///tmp/presidents.json' - content has been written here
```

You get the point. Now you're up to write your own workflows! 

Continue reading the [Getting Started Guide](doc/getting-started.md).

Contribute
----------

Contributions are welcome! Don't hesitate to suggest recipes.

This library is 100% covered with [Pest](https://pestphp.com) tests.

Please ensure to run tests using the command below and maintain code coverage before submitting PRs.

```bash
composer ci:check
```

License
-------

MIT.
