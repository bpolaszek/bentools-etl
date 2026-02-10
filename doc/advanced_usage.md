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

> [!IMPORTANT]
> As you can see:
> - Your transformer can _yield_ values, in case 1 extracted item becomes several items to load
> - You can use `EtlState.destination` to retrieve the second argument you passed yo `$etl->process()`.

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


Batch transforms
-----------------

By default, transformers process items one-by-one. But sometimes you want to process multiple items at once — for example, sending concurrent HTTP requests instead of waiting for each response sequentially.

The `BatchTransformerInterface` allows you to transform a batch of items in a single call:

```php
use BenTools\ETL\EtlConfiguration;
use BenTools\ETL\EtlExecutor;
use BenTools\ETL\EtlState;
use BenTools\ETL\Transformer\CallableBatchTransformer;

$executor = (new EtlExecutor())
    ->extractFrom($urlExtractor)
    ->transformWith(new CallableBatchTransformer(
        function (array $items, EtlState $state): array {
            // $items contains a batch of URLs (e.g., 10 at a time)
            // Send all HTTP requests concurrently
            $responses = $httpClient->sendConcurrent(
                array_map(fn ($url) => new Request('GET', $url), $items)
            );

            return array_map(
                fn ($response) => json_decode($response->getBody(), true),
                $responses
            );
        }
    ))
    ->loadInto($loader)
    ->withOptions(new EtlConfiguration(batchSize: 10))
    ->process($urls);
```

The `batchSize` option in `EtlConfiguration` controls how many items are grouped into each batch. Each batch is passed as an array to your transformer's `transform(array $items, EtlState $state): Generator` method.

> [!NOTE]
> `BatchTransformerInterface` is a **separate interface** from `TransformerInterface` — it does not extend it.
> When `batchSize` is configured but the transformer does not implement `BatchTransformerInterface`, batching is silently ignored.

You can also implement `BatchTransformerInterface` directly for more complex use cases:

```php
use BenTools\ETL\Transformer\BatchTransformerInterface;
use Generator;

final class ConcurrentApiTransformer implements BatchTransformerInterface
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
    ) {}

    public function transform(array $items, EtlState $state): Generator
    {
        $responses = $this->httpClient->sendConcurrent(
            array_map(fn ($item) => new Request('GET', $item['url']), $items)
        );

        foreach ($responses as $i => $response) {
            yield [...$items[$i], 'data' => json_decode($response->getBody(), true)];
        }
    }
}
```

> [!TIP]
> Each value yielded by the generator becomes an individual item for the load phase, so you can also implement fan-out (yielding more items than inputs).

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

Using ReactPHP (experimental)
----------------------------------

By using the `ReactStreamProcessor` recipe, you can use ReactPHP as the processor of your data.

> [!IMPORTANT]
> `react/stream` and `react/event-loop` are required for this to work.

With this processor, you can extract data from an `iterable` or a [React Stream](https://github.com/reactphp/stream): 
each item will be iterated within a [Loop tick](https://github.com/reactphp/event-loop#futuretick) instead of a blocking `while` loop.

This allows you, for example, to: 
- [Periodically](https://github.com/reactphp/event-loop#addperiodictimer) perform some stuff (with `Loop::addPeriodicTimer()`)
- Handle [POSIX signals](https://github.com/reactphp/event-loop#addsignal) (with `Loop::addSignal()`)
- Use [React streams](https://github.com/reactphp/stream), like a TCP / HTTP server, a Redis / MySQL connection, or a file stream, for an event-oriented approach.

Example with a TCP server:

```php
use BenTools\ETL\EtlConfiguration;
use BenTools\ETL\EventDispatcher\Event\ExtractEvent;
use BenTools\ETL\EventDispatcher\Event\InitEvent;
use React\Socket\ConnectionInterface;
use React\Socket\SocketServer;

use function BenTools\ETL\stdOut;
use function BenTools\ETL\useReact;

$socket = new SocketServer('127.0.0.1:7000');

$etl = useReact() // or (new EtlExecutor())->withRecipe(new ReactStreamProcessor());
    ->loadInto(stdOut())
    ->onInit(function (InitEvent $event) {
        /** @var ConnectionInterface $stream */
        $stream = $event->state->source;
        $stream->on('close', function () use ($event) {
            $event->state->stop(); // Will flush all pending items and gracefully stop the ETL for that connection
        });
    })
    ->withOptions(new EtlConfiguration(flushEvery: 1)) // Optionally, flush on each data event
    ->onExtract(function (ExtractEvent $event) {
        if (!preg_match('//u', $event->item)) {
            $event->state->skip(); // Ignore binary data
        }
    });

$socket->on('connection', $etl->process(...));
```
