[![Latest Stable Version](https://poser.pugx.org/bentools/etl/v/stable)](https://packagist.org/packages/bentools/etl)
[![License](https://poser.pugx.org/bentools/etl/license)](https://packagist.org/packages/bentools/etl)
[![Build Status](https://img.shields.io/travis/bpolaszek/bentools-etl/master.svg?style=flat-square)](https://travis-ci.org/bpolaszek/bentools-etl)
[![Code Coverage](https://scrutinizer-ci.com/g/bpolaszek/bentools-etl/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/bpolaszek/bentools-etl/?branch=master)
[![Quality Score](https://img.shields.io/scrutinizer/g/bpolaszek/bentools-etl.svg?style=flat-square)](https://scrutinizer-ci.com/g/bpolaszek/bentools-etl)
[![Total Downloads](https://poser.pugx.org/bentools/etl/downloads)](https://packagist.org/packages/bentools/etl)

This **PHP 7.1+** library provides a very simple implementation of the `Extract / Transform / Load` pattern. 

It is heavily inspired by the [knplabs/etl](https://github.com/docteurklein/php-etl) library, with a more generic approach and less dependencies.

Overview
--------
```php
require_once __DIR__ . '/vendor/autoload.php';

use BenTools\ETL\Event\ContextElementEvent;
use BenTools\ETL\Extractor\IncrementorExtractor;
use BenTools\ETL\Loader\DebugLoader;
use BenTools\ETL\Runner\ETLRunner;
use BenTools\ETL\Transformer\CallbackTransformer;

$items     = [
    'France',
    'Germany',
    'Poland',
];
$extract   = new IncrementorExtractor();
$transform = new CallbackTransformer('strtolower');
$load      = new DebugLoader();
$run       = new ETLRunner();
$run->onExtract(function (ContextElementEvent $event) {
    $element = $event->getElement();
    if ('Germany' === $element->getData()) {
        $element->skip();
    }
});
$run($items, $extract, $transform, $load);
```

Output:
```php
array(2) {
  [0]=>
  string(6) "france"
  [2]=>
  string(7) "poland"
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

[Extractors](doc/Extractors.md)

[Transformers](doc/Transformers.md)

[Loaders](doc/Loaders.md)

[Events](doc/Events.md)

[Advanced CSV to JSON conversion example](doc/Recipes/AdvancedCSVToJSON.md)


License
-------

MIT
