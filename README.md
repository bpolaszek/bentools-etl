[![Latest Unstable Version](http://poser.pugx.org/bentools/etl/v/unstable)](https://packagist.org/packages/bentools/etl)
[![Latest Stable Version](https://poser.pugx.org/bentools/etl/v/stable)](https://packagist.org/packages/bentools/etl)
[![License](https://poser.pugx.org/bentools/etl/license)](https://packagist.org/packages/bentools/etl)
[![CI Workflow](https://github.com/bpolaszek/bentools-etl/actions/workflows/ci.yml/badge.svg)](https://github.com/bpolaszek/bentools-etl/actions/workflows/ci.yml)
[![Coverage](https://codecov.io/gh/bpolaszek/bentools-etl/branch/master/graph/badge.svg?token=L5ulTaymbt)](https://codecov.io/gh/bpolaszek/bentools-etl)
[![Total Downloads](https://poser.pugx.org/bentools/etl/downloads)](https://packagist.org/packages/bentools/etl)

Okay, so you heard about the [Extract / Transform / Load](https://en.wikipedia.org/wiki/Extract,_transform,_load) pattern,
and you're looking for a PHP library to do the stuff.

Alright, let's go!

Installation
------------

```bash
composer require bentools/etl:^4.0@alpha
```

_Warning: version  4.0 is a complete rewrite and a involves important BC breaks._

_Don't upgrade from `^2.0` or `^3.0` unless you know what you're doing!_

Usage
-----

Let's cover the basic concepts:
- **Extract**: you have a source of data (a database, a CSV file, whatever) - an **extractor** is able to read that data and provide an iterator of items
- **Transform**: apply transformation to each item. A **transformer** may generate 0, 1 or several items to **load** (for example, 1 item may generate multiple SQL queries)
- **Load**: load transformed item to the destination. For example, **extracted items** have been **transformed** to SQL queries, and your **loader** will run those queries against your database.

Now let's have a look on how simple it is:

```php
use BenTools\ETL\EtlExecutor;

$etl = (new EtlExecutor())
    ->transformWith(fn (string $name) => strtoupper($name));

$singers = ['Bob Marley', 'Amy Winehouse'];
$report = $etl->process($singers);
dump($report->output); // ["BOB MARLEY", "AMY WINEHOUSE"]
```

OK, that wasn't really hard, here we basically don't have to extract anything (we can already iterate on `$singers`),
and we're not loading anywhere, except into PHP's memory.

Now let's take this to the next level:

```csv
city_english_name,city_local_name,country_iso_code,continent,population
"New York","New York",US,"North America",8537673
"Los Angeles","Los Angeles",US,"North America",39776830
Tokyo,東京,JP,Asia,13929286
```

```php
use BenTools\ETL\EtlExecutor;

$etl = (new EtlExecutor())
    ->extractFrom(new CSVExtractor(options: ['columns' => 'auto']))
    ->loadInto(new JSONLoader());

$report = $etl->process('file:///tmp/cities.csv', 'file:///tmp/cities.json');
dump($report->output); // file:///tmp/cities.json
```

Then, let's have a look at `/tmp/cities.json`:
```json
[
    {
        "city_english_name": "New York",
        "city_local_name": "New York",
        "country_iso_code": "US",
        "continent": "North America",
        "population": 8537673
    },
    {
        "city_english_name": "Los Angeles",
        "city_local_name": "Los Angeles",
        "country_iso_code": "US",
        "continent": "North America",
        "population": 39776830
    },
    {
        "city_english_name": "Tokyo",
        "city_local_name": "東京",
        "country_iso_code": "JP",
        "continent": "Asia",
        "population": 13929286
    }
]
```

Notice that we didn't _transform_ anything here, we just denormalized the CSV file to an array, then serialized that array to a JSON file.

The `CSVExtractor` has some options to _read_ the data, such as considering that the 1st row is the column keys.

Creating your own Extractor / Transformers / Loaders
--------------------------------------------------

You can implement `ExtractorInterface`, `TransformerInterface` and `LoaderInterface`, or basically use simple `callable` with the same signatures.

Here's another example:
```php
$pdo = new \PDO('mysql:host=localhost;dbname=cities');
$etl = (new EtlExecutor())
    ->extractFrom(new CSVExtractor(options: ['columns' => 'auto']))
    ->transformWith(function (mixed $city) {
        yield [
            'INSERT INTO countries (country_code, continent) VALUES (?, ?)',
            [$city['country_iso_code'], $city['continent']],
        ];
        yield [
            'INSERT INTO cities (english_name, local_name, country_code, population)',
            [$city['city_english_name'], $city['city_local_name'], $city['country_code'], $city['population']],
        ];
    })
    ->loadInto(function (array $query, EtlState $state) {
        /** @var \PDO $pdo */
        $pdo = $state->destination; // See below - $state->destination corresponds to the $destination argument of the $etl->process() method.
        [$sql, $params] = $query;
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
    });
$etl->process('file:///tmp/cities.csv', $pdo);
```

As you can see:
- Your transformer can _yield_ values, in case 1 extracted item becomes several items to load
- You can use `EtlState.destination` to retrieve the second argument you passed yo `$etl->process()`.

The `EtlState` object contains all elements relative to the state of your ETL workflow being running.

Difference between `yield` and `return` in transformers
------------------------------------------------------

The `EtlExecutor::transformWith()` method accepts an unlimited number of transformers as arguments.

When you chain transformers, keep in mind that every transformer will get:
- Either the returned value passed from the previous transformer
- Either an array of every yielded value from the previous transformer

But the last transformer of the chain (or your only one transformer) is deterministic to know what will be passed to the loader (either a return  value, or a generator):
- If your transformer `returns` a value, this value will be passed to the loader.
- If your transformer `returns` an array of values (or whatever iterable), that return value will be passed to the loader.
- If your transformer `yields` values, each yielded value will be passed to the loader.

Using events
------------

The `EtlExecutor` emits various events during the workflow:
- `InitEvent` when `process()` was just called
- `StartEvent` when extraction just started (we might know the total number of items to extract at this time, if the extractor provides this)
- `ExtractEvent` upon each extracted item
- `ExtractExceptionEvent` when something wrong occured during extraction (this is generally not recoverable)
- `TransformEvent` upon each transformed item
- `TransformExceptionEvent` when something wrong occured during transformation (the exception can be dismissed)
- `LoadEvent` upon each loaded item
- `LoadExceptionEvent` when something wrong occured during loading (the exception can be dismissed)
- `FlushEvent` at each flush
- `FlushExceptionEvent` when something wrong occured during flush (the exception can be dismissed)
- `EndEvent` whenever the workflow is complete.

All events give you access to the `EtlState` object, the state of the running ETL process, which allows you to read what's going on 
(total number of items, number of loaded items, current extracted item index), write any arbitrary data into the `$state->context` array, 
[skip items](#skipping-items), [stop the workflow](#stopping-the-workflow), and [trigger an early flush](#flush-frequency-and-early-flushes).

You can hook to those events during `EtlExecutor` instantiation, i.e.:

```php
$etl = (new EtlExecutor())
    ->onExtract(
        fn (ExtractEvent $event) => $logger->info('Extracting item #{key}', ['key' => $event->state->currentItemKey]),
    );
```

Skipping items
--------------

You can skip items at any time.

Use the `$state->skip()` method from the `EtlState` object as soon as your business logic requires it.

Stopping the workflow
---------------------

You can stop the workflow at any time.

Use the `$state->stop()` method from the `EtlState` object as soon as your business logic requires it.

Flush frequency and early flushes
---------------------------------

By default, the `flush()` method of your loader will be invoked at the end of the ETL, 
meaning it will likely keep all loaded items in memory before dumping them to their final destination.

Feel free to adjust a `flushFrequency` that fits your needs 
and optionally trigger an early flush at any time during the ETL process:

```php
$etl = (new EtlExecutor(options: new EtlConfiguration(flushFrequency: 10)))
    ->onLoad(
        function (LoadEvent $event) {
            if (/* whatever reason */) {
                $event->state->flush();
            }
        },
    );
```

Recipes
-------

Recipes are reusable configurations of an `EtlExecutor`.
For example, to enable logging, use the `LoggerRecipe`:

```php
use BenTools\ETL\EtlExecutor;
use BenTools\ETL\Recipe\LoggerRecipe;
use Monolog\Logger;

$logger = new Logger();
$etl = (new EtlExecutor())
    ->withRecipe(new LoggerRecipe($logger));
```

This will basically listen to all events and fire log entries.

### Creating your own recipes
You can create your own recipes by implementing `BenTools\ETL\Recipe\Recipe` or using a callable with the same signature.

Example for displaying a progress bar when using the Symfony framework:

```php
use BenTools\ETL\EtlExecutor;
use BenTools\ETL\EventDispatcher\Event\Event;
use BenTools\ETL\Recipe\Recipe;
use Symfony\Component\Console\Helper\ProgressBar;

final class ProgressBarRecipe extends Recipe
{
    public function __construct(
        public readonly ProgressBar $progressBar,
    ) {
    }

    public function decorate(EtlExecutor $executor): EtlExecutor
    {
        return $executor
            ->onStart(function (Event $event) {
                if (!$event->state->nbTotalItems) {
                    return;
                }
                $this->progressBar->setMaxSteps($event->state->nbTotalItems);
            })
            ->onExtract(fn () => $this->progressBar->advance())
            ->onEnd(fn () => $this->progressBar->finish());
    }
}
```

Usage:

```php
use BenTools\ETL\EtlExecutor;
use Symfony\Component\Console\Style\SymfonyStyle;

$output = new SymfonyStyle($input, $output);
$progressBar = $output->createProgressBar();
$executor = (new EtlExecutor())->withRecipe(new ProgressBarRecipe($progressBar));
```

Contribute
----------

Contributions are welcome! 
Before sending your PRs, run this command to ensure test pass and 100% of the code is covered.

```bash
composer ci:check
```

License
-------

MIT.
