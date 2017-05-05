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

TextLinesIterator
-----------------
Takes a string, and yields each line.

Example:
```php
require_once __DIR__ . '/vendor/autoload.php';

use BenTools\ETL\Iterator\TextLinesIterator;

$text = <<<EOF
foo


bar
EOF;

$iterator = new TextLinesIterator($text);
foreach ($iterator as $item) {
    var_dump($item);
}
```

Outputs:
```
string(3) "foo"
string(3) "bar"

```

By default, empty lines are skipped. This can be prevented with `new TextLinesIterator($text, false)`.


CsvStringIterator
-----------------
Takes a `TextLinesIterator` as an argument (or use the the `createFromText()` factory) and returns an indexed array of each csv line.

Example:
```php
use BenTools\ETL\Iterator\CsvStringIterator;

require_once __DIR__ . '/vendor/autoload.php';

$text = <<<EOF
country,name
USA,"Donald Trump"
Russia,"Vladimir Poutine"
EOF;


$iterator = CsvStringIterator::createFromText($text);
foreach ($iterator AS $item) {
    var_dump($item);
}
```

Outputs:
```
array(2) {
  [0]=>
  string(7) "country"
  [1]=>
  string(4) "name"
}
array(2) {
  [0]=>
  string(3) "USA"
  [1]=>
  string(12) "Donald Trump"
}
array(2) {
  [0]=>
  string(6) "Russia"
  [1]=>
  string(16) "Vladimir Poutine"
}
```

CsvFileIterator
---------------
Takes an `SplFileObject` as an argument (or use the `createFromFileName()` factory) and returns an indexed array of each csv line.

Example:

```php
use BenTools\ETL\Iterator\CsvFileIterator;

require_once __DIR__ . '/vendor/autoload.php';

$iterator = CsvFileIterator::createFromFilename('dictators.csv', ',');
foreach ($iterator AS $item) {
    var_dump($item);
}
```

Outputs:
```
array(2) {
  [0]=>
  string(7) "country"
  [1]=>
  string(4) "name"
}
array(2) {
  [0]=>
  string(3) "USA"
  [1]=>
  string(12) "Donald Trump"
}
array(2) {
  [0]=>
  string(6) "Russia"
  [1]=>
  string(16) "Vladimir Poutine"
}
```

KeysAwareCsvIterator
--------------------
This iterator uses the _Decorator_ pattern to wrap a CSV Iterator (`CsvFileIterator` or `CsvStringIterator`) and allows you to:

* Specify the array keys to apply to each row
* Specify that the keys are set in the 1st line of the CSV
* Skip the 1st line of the CSV (useful indeed is the 1st line represent the keys)

Example:
```php
use BenTools\ETL\Iterator\CsvStringIterator;
use BenTools\ETL\Iterator\KeysAwareCsvIterator;

require_once __DIR__ . '/vendor/autoload.php';

$text = <<<EOF
country,name
USA,"Donald Trump"
Russia,"Vladimir Poutine"
EOF;


$iterator = new KeysAwareCsvIterator(CsvStringIterator::createFromText($text));
foreach ($iterator AS $item) {
    var_dump($item);
}
```

Outputs:
```
array(2) {
  ["country"]=>
  string(3) "USA"
  ["name"]=>
  string(12) "Donald Trump"
}
array(2) {
  ["country"]=>
  string(6) "Russia"
  ["name"]=>
  string(16) "Vladimir Poutine"
}
```

Another example:
```php
use BenTools\ETL\Iterator\CsvStringIterator;
use BenTools\ETL\Iterator\KeysAwareCsvIterator;

require_once __DIR__ . '/vendor/autoload.php';

$text = <<<EOF
country,name
USA,"Donald Trump"
Russia,"Vladimir Poutine"
EOF;

$keys = [
    'country_name',
    'actual_president'    
];

$iterator = new KeysAwareCsvIterator(CsvStringIterator::createFromText($text), $keys, true);
foreach ($iterator AS $item) {
    var_dump($item);
}
```

Outputs:
```
array(2) {
  ["country_name"]=>
  string(3) "USA"
  ["actual_president"]=>
  string(12) "Donald Trump"
}
array(2) {
  ["country_name"]=>
  string(6) "Russia"
  ["actual_president"]=>
  string(16) "Vladimir Poutine"
}
```