Extractors
==========

Extractors are kind of factories: when you iterate over a PHP loop, like `foreach ($items AS $key => $value)`, you get a `$key` and a `$value`. 

However, the real way to identify of your resource may not be the `$key` itself, but something within your `$value` (`$value->getId()` for instance).

Here comes the **ContextElement**. A **ContextElement** carries _your_ id (which may, or may not, differ from `$key`), and the data associated (`$value`). 

As a **ContextElement** factory, the role of an extractor is to return a `BenTools\ETL\Context\ContextElementInterface` object and hydrate its id and its data.

To respect the ETL pattern, if you define your own extractors, it's also their responsibility to validate the source data (and they might throw exceptions or call `$contextElement->skip()` or `$contextElement->stop()` if this element is blocking the whole loop).

To make things simpler we provide a default `BenTools\ETL\Context\ContextElement` class and default extractors:

KeyValueExtractor
-----------------
This is the most basic extractor, since it extracts the key and the value provided by the iterator.

```php
require_once __DIR__ . '/vendor/autoload.php';

use BenTools\ETL\Extractor\KeyValueExtractor;
use BenTools\ETL\Loader\DebugLoader;

$items   = [
    'foo' => 'bar',
    'bar' => 'baz'
];
$extract = new KeyValueExtractor();
$load  = new DebugLoader();
foreach ($items AS $key => $value) {
    $element = $extract($key, $value);
    $load($element);
}
$load->flush();
```

Ouputs:
```php
array (size=2)
  'foo' => string 'bar' (length=3)
  'bar' => string 'baz' (length=3)
```

IncrementorExtractor
--------------------

This extractors provides an incremental key.

```php
require_once __DIR__ . '/vendor/autoload.php';

use BenTools\ETL\Extractor\IncrementorExtractor;
use BenTools\ETL\Loader\DebugLoader;

$items   = [
    'foo' => 'bar',
    'bar' => 'baz'
];
$extract = new IncrementorExtractor();
$load  = new DebugLoader();
foreach ($items AS $key => $value) {
    $element = $extract($key, $value);
    $load($element);
}
$load->flush();
```

Outputs:
```php
array (size=2)
  0 => string 'bar' (length=3)
  1 => string 'baz' (length=3)
```

ArrayPropertyExtractor
----------------------
When the value of each element of the loop is an array, you can specify which array key will be used to generate the id.

```php
require_once __DIR__ . '/vendor/autoload.php';

use BenTools\ETL\Extractor\ArrayPropertyExtractor;
use BenTools\ETL\Loader\DebugLoader;

$items   = [
    'foo' => [
        'id' => '6ef02334-002e-11e7-93ae-92361f002671',
        'name' => 'Foo'
    ],
    'bar' => [
        'id' => 'a55b81da-f270-4de0-907a-25488e5ffcc8',
        'name' => 'Bar'
    ],
];
$extract = new ArrayPropertyExtractor('id');
$load  = new DebugLoader();
foreach ($items AS $key => $value) {
    $element = $extract($key, $value);
    $load($element);
}
$load->flush();
```

Outputs:
```php
array (size=2)
  '6ef02334-002e-11e7-93ae-92361f002671' => 
    array (size=1)
      'name' => string 'Foo' (length=3)
  'a55b81da-f270-4de0-907a-25488e5ffcc8' => 
    array (size=1)
      'name' => string 'Bar' (length=3)
```

As a consequence, the `$value['id']` is unset. You can prevent this behaviour by setting the 2nd argument to false: `new ArrayPropertyExtractor('id', false);`
 

ObjectPropertyExtractor
-----------------------
The same, for objects with public properties. Note that it's not possible to unset `$value->id` in that case.
```php
require_once __DIR__ . '/vendor/autoload.php';

use BenTools\ETL\Extractor\ObjectPropertyExtractor;
use BenTools\ETL\Loader\DebugLoader;

$foo = new stdClass();
$foo->id = '6ef02334-002e-11e7-93ae-92361f002671';
$foo->name = 'Foo';

$bar = new stdClass();
$bar->id = 'a55b81da-f270-4de0-907a-25488e5ffcc8';
$bar->name = 'Bar';

$items   = [
    'foo' => $foo,
    'bar' => $bar,
];
$extract = new ObjectPropertyExtractor('id');
$load  = new DebugLoader();
foreach ($items AS $key => $value) {
    $element = $extract($key, $value);
    $load($element);
}
$load->flush();
```

Outputs:
```php
array (size=2)
  '6ef02334-002e-11e7-93ae-92361f002671' => 
    object(stdClass)[3]
      public 'id' => string '6ef02334-002e-11e7-93ae-92361f002671' (length=36)
      public 'name' => string 'Foo' (length=3)
  'a55b81da-f270-4de0-907a-25488e5ffcc8' => 
    object(stdClass)[2]
      public 'id' => string 'a55b81da-f270-4de0-907a-25488e5ffcc8' (length=36)
      public 'name' => string 'Bar' (length=3)
```

CallbackExtractor
-----------------
This extractor uses a callback on a `ContextElement` created by a `KeyValueExtractor` to allow you to define the key and the value.
```php
require_once __DIR__ . '/vendor/autoload.php';

use BenTools\ETL\Context\ContextElementInterface;
use BenTools\ETL\Loader\DebugLoader;

class MyObject {
    private $id, $name;
    public function __construct($id, $name) {
        $this->id   = $id;
        $this->name = $name;
    }

    public function getId() {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getName() {
        return $this->name;
    }
}

$foo = new MyObject('6ef02334-002e-11e7-93ae-92361f002671', 'Foo');
$bar = new MyObject('a55b81da-f270-4de0-907a-25488e5ffcc8', 'Bar');

$items   = [
    'foo' => $foo,
    'bar' => $bar,
];
$extract = new \BenTools\ETL\Extractor\CallbackExtractor(function (ContextElementInterface $element) {
    /** @var MyObject $myObject */
    $myObject = $element->getData();
    $element->setId($myObject->getId());
});
$load  = new DebugLoader();
foreach ($items AS $key => $value) {
    $element = $extract($key, $value);
    $load($element);
}
$load->flush();
```

Outputs:
```php
array (size=2)
  '6ef02334-002e-11e7-93ae-92361f002671' => 
    object(MyObject)[3]
      private 'id' => string '6ef02334-002e-11e7-93ae-92361f002671' (length=36)
      private 'name' => string 'Foo' (length=3)
  'a55b81da-f270-4de0-907a-25488e5ffcc8' => 
    object(MyObject)[2]
      private 'id' => string 'a55b81da-f270-4de0-907a-25488e5ffcc8' (length=36)
      private 'name' => string 'Bar' (length=3)
```

Next: [Transformers](Transformers.md)