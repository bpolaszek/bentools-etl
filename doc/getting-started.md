# Getting started

Consider you have a `/tmp/cities.csv` file containing this, and you want to convert it to a JSON file.


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

This libary ships with a few built-in extractors and loaders (plain text, csv, json) to name a few,
but you can of course create your own. See [Advanced Usage](advanced_usage.md).

The `EtlState` object
---------------------

The `ETLState` object is the state of the ETL which is currently processed by the `EtlExecutor`.
This object gives you various information such as the duration, the total number of items, 
the current extracted key, and so on.
It also contains a `context` array which is here to hold some data related to the current process.

The `ETLState` object is injected in extractors' `extract()` method, 
in transformers' `transform()` method
and in loaders' `load()` and `flush()` methods.
If you use callables, it will be injected as well.

The `ETLState` object is also injected into all events.
Most of its properties are read-only, except `context`.

Skipping items
--------------

You can skip items at any time.

Use the `$state->skip()` method from the `EtlState` object as soon as your business logic requires it.

Stopping the workflow
---------------------

You can stop the workflow at any time.

Use the `$state->stop()` method from the `EtlState` object as soon as your business logic requires it.

Using Events
------------

The `EtlExecutor` emits a variety of events during the ETL workflow, providing insights and control over the process.

- `InitEvent` when `process()` was just called
- `StartEvent` when extraction just started (we might know the total number of items to extract at this time, if the extractor provides this)
- `ExtractEvent` upon each extracted item
- `ExtractExceptionEvent` when something wrong occured during extraction (this is generally not recoverable)
- `TransformEvent` upon each transformed item (exposes a `TransformResult` object, containing 0, one or more items to load)
- `TransformExceptionEvent` when something wrong occured during transformation (the exception can be dismissed)
- `BeforeLoadEvent` upon each item to be loaded
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

Flush frequency and early flushes
---------------------------------

By default, the `flush()` method of your loader will be invoked at the end of the ETL,
meaning it will likely keep all loaded items in memory before dumping them to their final destination.

Feel free to adjust a `flushFrequency` that fits your needs to manage memory usage and data processing efficiency
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

Advanced usage
--------------

See [Advanced Usage](advanced_usage.md).
