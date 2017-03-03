Iterators
=========

By default, you are free to use any  [`iterable`](https://wiki.php.net/rfc/iterable) array, object or generator - anything that can go in a `foreach` loop.

To simplify some use cases, you can also use those ones:

JsonIterator
------------
```php
require_once __DIR__ . '/vendor/autoload.php';

use BenTools\ETL\Iterator\JsonIterator;

$iterator = new JsonIterator('{"cat":"meow","dog":"bark"}'); // Also accepts an already-decoded JSON
foreach ($iterator AS $key => $value) {
    var_dump(sprintf('The %s %ss', $key, $value));
}
```

Outputs:
```php
string 'The cat meows' (length=13)
string 'The dog barks' (length=13)
```

CsvFileIterator
---------------
There's serveral ways to iterate over a CSV file, you'd better have a look at our [Recipes](Recipes/AdvancedCSVToJSON.md).