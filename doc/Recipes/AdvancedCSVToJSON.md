Advanced example
================

Convert a **CSV** to a **JSON** and apply some transformations.

The goal:
---------

Transform this:
```csv
Variable Name,Dataset,Code List,Definition
bmu,Community engagement,,Number of TB Basic Management Units in the country
community_data_available,Community engagement,A=No; B=Yes,Are data available on community-based referrals or any form of community treatment adherence support?
prevtx_data_available,Latent TB infection,A=No; E= Yes - available from the routine surveillance system; G=Yes - estimated from a survey ,Are data available on the number of children aged under 5 who are household contacts of TB cases and started on TB preventive therapy?

```

into this:
```json
{
    "bmu": {
        "dataset": "Community engagement",
        "code_list": [],
        "definition": "Number of TB Basic Management Units in the country"
    },
    "community_data_available": {
        "dataset": "Community engagement",
        "code_list": {
            "A": "No",
            "B": "Yes"
        },
        "definition": "Are data available on community-based referrals or any form of community treatment adherence support?"
    },
    "prevtx_data_available": {
        "dataset": "Latent TB infection",
        "code_list": {
            "A": "No",
            "E": "Yes - available from the routine surveillance system",
            "G": "Yes - estimated from a survey"
        },
        "definition": "Are data available on the number of children aged under 5 who are household contacts of TB cases and started on TB preventive therapy?"
    }
}
```

The challenge:
--------------

* We want to skip the 1st row
* We want to use the 1st row as keys, and slug them
* We want to use the 1st column as the identifier of each row
* We do not want the 1st column to be part of the value
* We want the "Code list" column to be output as an associative array

How to achieve this:
--------------------

* We'll use `BenTools\ETL\Iterator\CsvFileIterator` to iterate over the CSV (we'll get an indexed array for each row)
* We'll use `BenTools\ETL\Extractor\ArrayPropertyExtractor` to use the 1st column as an identifier
* We'll hook on the `BenTools\ETL\Event\ETLEvents::AFTER_EXTRACT` event to create the keys and skip the 1st row
* We'll use an external library `Cocur\Slugify\Slugify` to make slugs from our keys
* We'll use a `callable` Transformer to create an associative array for each row (by combining keys and values) and transform the code list to an array

The code:
-------
```php
use BenTools\ETL\Context\ContextElementInterface;
use BenTools\ETL\Event\ETLEvents;
use BenTools\ETL\Event\EventDispatcher\Bridge\Symfony\SymfonyEvent;
use BenTools\ETL\Event\EventDispatcher\Bridge\Symfony\SymfonyEventDispatcherBridge as SymfonyBridge;
use BenTools\ETL\Extractor\ArrayPropertyExtractor;
use BenTools\ETL\Iterator\CsvFileIterator;
use BenTools\ETL\Loader\JsonFileLoader;
use BenTools\ETL\Runner\ETLRunner;
use Cocur\Slugify\Slugify;

require_once __DIR__ . '/vendor/autoload.php';

// We will iterate over the CSV rows.
$csvInput    = new CsvFileIterator(new SplFileObject(__DIR__ . '/input/input.csv'));

// We will extract each row - the 1st column (index 0) will define the identifier of each row.
$extractor   = new ArrayPropertyExtractor(0, $shift = true);

// We will load the data in a JSON file
$loader = new JsonFileLoader(new SplFileObject(__DIR__ . '/output/output.json', 'w'), JSON_PRETTY_PRINT);

// Here's our transformer function.
$transformer = function (ContextElementInterface $element) use (&$keys) {

    // Combine keys (1st row) and values (current row)
    $data = array_combine($keys, $element->getData());

    // Process code list
    $codeList = array_map('trim', explode(';', trim($data['code_list'])));
    $data['code_list'] = [];
    foreach ($codeList AS $code) {
        if (false !== strpos($code, '=')) {
            list($key, $value) = explode('=', $code);
            $data['code_list'][trim($key)] = trim($value);
        }
    }

    // Hydrate back our element
    $element->setData($data);
};

// We need to hook on the BenTools\ETL\Event\ETLEvents::AFTER_EXTRACT event to generate the keys
$eventDispatcher = new SymfonyBridge();
$keys            = [];
$eventDispatcher->getWrappedDispatcher()->addListener(ETLEvents::AFTER_EXTRACT, function (SymfonyEvent $event) use (&$keys) {

    // The 1st CSV row will give us the keys
    if (empty($keys)) {

        // The Symfony event wraps the "real" event.
        $event = $event->getWrappedEvent();

        // Retrieve element
        $contextElement = $event->getElement();

        // Remove spaces and caps
        $slugify = function ($key) {
            return Slugify::create()->slugify($key, ['separator' => '_']);
        };
        $keys = array_map($slugify, array_values($contextElement->getData()));

        // We don't want that row to be sent to the loader.
        $contextElement->skip();
    }
});

$run = new ETLRunner(null, $eventDispatcher);
$run($csvInput, $extractor, $transformer, $loader);


```