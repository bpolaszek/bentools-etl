Getting started: A simple example
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


Next: [Events](Events.md)
