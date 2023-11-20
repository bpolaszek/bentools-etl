# Recipes

Recipes are pre-configured setups for `EtlExecutor`, facilitating reusable ETL configurations.

LoggerRecipe
------------

The `LoggerRecipe` enables logging for all ETL events.

```php
use BenTools\ETL\EtlExecutor;
use BenTools\ETL\Recipe\LoggerRecipe;
use Monolog\Logger;

$logger = new Logger();
$etl = (new EtlExecutor())
    ->withRecipe(new LoggerRecipe($logger));
```

This will basically listen to all events and fire log entries.

FilterRecipe
------------

The `FilterRecipe` gives you syntactic sugar for skipping items.

```php
use BenTools\ETL\EtlExecutor;
use BenTools\ETL\Recipe\LoggerRecipe;
use Monolog\Logger;

use function BenTools\ETL\skipWhen;

$logger = new Logger();
$etl = (new EtlExecutor())->withRecipe(skipWhen(fn ($item) => 'apple' === $item));
$report = $etl->process(['banana', 'apple', 'pinapple']);

var_dump($report->output); // ['banana', 'pineapple']
```

Creating your own recipes
-------------------------

You can create your own recipes by implementing `BenTools\ETL\Recipe\Recipe` 
or using a callable with the same signature.

### Example 1. Stop the workflow when a max number of items has been reached

```php
use BenTools\ETL\EtlExecutor;
use BenTools\ETL\EtlState;
use BenTools\ETL\EventDispatcher\Event\ExtractEvent;

use const PHP_INT_MAX;

final class MaxItemsRecipe extends Recipe
{
    public function __construct(
        private readonly int $maxItems = PHP_INT_MAX,
    ) {
    }

    public function decorate(EtlExecutor $executor): EtlExecutor
    {
        return $executor
            ->withContext(['maxItems' => $this->maxItems])
            ->onExtract($this);
    }

    public function __invoke(ExtractEvent $event): void
    {
        if ($event->state->nbExtractedItems >= $event->state->context['maxItems']) {
            $event->state->nextTick(fn (EtlState $state) => $state->skip());
        }
    }
}
```

Usage:

```php
use function BenTools\ETL\withRecipe;

$etl = withRecipe(new MaxItemsRecipe(10)); // Set to 10 items max by default
$report = $etl->process(['foo', 'bar', 'baz'], context: ['maxItems' => 2]); // Optionally overwrite here
var_dump($report->output); // ['foo', 'bar']
```

### Example 2. Display a progress bar when using the Symfony framework:

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
