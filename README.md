This **PHP 7.1+** library provides a very simple implementation of the `Extract / Transform / Load` pattern. 

It is heavily inspired by the [knplabs/etl](https://github.com/docteurklein/php-etl) library, with a more generic approach and less dependencies.

Overview
--------
```php
require_once __DIR__ . '/vendor/autoload.php';

use BenTools\ETL\Extractor\KeyValueExtractor;
use BenTools\ETL\Loader\DebugLoader;
use BenTools\ETL\Runner\ETLRunner;
use BenTools\ETL\Transformer\CallbackTransformer;

$items     = [
    'France',
    'Germany'
];
$extract   = new KeyValueExtractor();
$transform = new CallbackTransformer('strtolower');
$load      = new DebugLoader();
$run       = new ETLRunner();
$run($items, $extract, $transform, $load);
```

Output:
```php
array(2) {
  [0]=>
  string(6) "france"
  [1]=>
  string(7) "germany"
}

```

Installation
------------

```
composer require  bentools/etl
```

Tests
-----

```
./vendor/bin/phpunit
```


Documentation and recipes
-------------------------
[Concept](doc/Concept.md)

[Getting started](doc/GettingStarted.md)

[Events](doc/Events.md)

[Advanced CSV to JSON conversion example](doc/Recipes/AdvancedCSVToJSON.md)


License
-------

MIT
