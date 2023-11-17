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
