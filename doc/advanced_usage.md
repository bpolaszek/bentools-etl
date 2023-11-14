# Advanced usage

Creating your own Extractor / Transformers / Loaders
--------------------------------------------------

You can implement `ExtractorInterface`, `TransformerInterface`, and `LoaderInterface`.
Alternatively, use simple `callable` functions with the same signatures.

Example:
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

A transformer can either return a value, or yield values (like the example above).

The `EtlExecutor::transformWith()` method accepts an unlimited number of transformers as arguments.

When you chain transformers, keep in mind that every transformer will get:
- Either the returned value passed from the previous transformer
- Either a `Generator` of every yielded value from the previous transformer

But the last transformer of the chain (or your only one transformer) is deterministic to know what will be passed to the loader:
- If your transformer `returns` a value, this value will be passed to the loader (and the loader will be called once for this value).
- If your transformer `returns` an array of values (or whatever iterable), that return value will be passed to the loader (and the loader will be called once for this value).
- If your transformer `yields` values, each yielded value will be passed to the loader (and the loader will be called for each yielded value).



Next tick
---------

You can also access the `EtlState` instance of the next item to be processed, for example to trigger
an early flush on the next item, or to stop the whole process once the current item will be loaded.

Example:

```php
use BenTools\ETL\EventDispatcher\Event\LoadEvent;

$etl = $etl->onLoad(function (LoadEvent $event) {
    $item = $event->item;
    if (/* some reason */) {
        $event->state->flush(); // Request early flush after loading
        $event->state->nextTick(function (EtlState $state)  use ($item) {
            // $item will be flushed, so we can do something with it
            var_dump($item->id);
        });
    }
});
```

Chaining extractors / transformers / loaders
-------------------------------------------

Instead of replacing existing extractors / transformers / loaders inside your `EtlExecutor`,
you can decorate them by using the `chain` function:

```php
use BenTools\ETL\EtlExecutor;
use ArrayObject;

use function BenTools\ETL\chain;
use function implode;
use function str_split;
use function strtoupper;

$a = new ArrayObject();
$executor = (new EtlExecutor())
    ->extractFrom(fn () => yield 'foo')
    ->transformWith(fn (string $value) => strtoupper($value))
    ->loadInto(fn (string $value) => $a->append($value));

$b = new ArrayObject();
$executor = $executor
    ->extractFrom(
        chain($executor->extractor)->with(fn () => ['bar'])
    )
    ->transformWith(
        chain($executor->transformer)->with(fn (string $value) => implode('-', str_split($value)))
    )
    ->loadInto(
        chain($executor->loader)->with(fn (string $value) => $b->append($value))
    );

$executor->process();
var_dump([...$a]); // ['F-O-O', 'B-A-R']
var_dump([...$b]); // ['F-O-O', 'B-A-R']
```

Reading from STDIN / Writing to STDOUT
--------------------------------------

Easy as hell.

```php
use function BenTools\ETL\stdIn;
use function BenTools\ETL\stdOut;
use function BenTools\ETL\transformWith;

transformWith(fn (string $line) => strtoupper($line))
    ->extractFrom(stdIn())
    ->loadInto(stdOut())
    ->process();
```

Recipes
-------

You can create reusable ETL configurations (extractors, transformers, loaders, event listeners, ...).

See [Recipes](recipes.md).

Instantiators
-------------

You can use the `extractFrom()`, `transformWith()`, `loadInto()` and `withRecipe()` functions
to instantiate an `EtlExecutor`.

Example:

```php
use BenTools\ETL\Recipe\LoggerRecipe;
use Monolog\Logger;

use function BenTools\ETL\withRecipe;

$logger = new Logger();
$report = withRecipe(new LoggerRecipe($logger))
    ->transformWith(fn ($value) => strtoupper($value))
    ->process(['foo', 'bar']);
```
