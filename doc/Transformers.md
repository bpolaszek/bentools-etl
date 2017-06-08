Transformers
============

This library voluntarily does not provide any transformer. You have to implement your owns.

Nevertheless, if you do not need to change the id, you can use our `CallbackTransformer` to apply modification on your data:

```php
require_once __DIR__ . '/vendor/autoload.php';

use BenTools\ETL\Context\ContextElement;
use BenTools\ETL\Transformer\CallbackTransformer;

$element = new ContextElement('foo', 'bar');
$transform = new CallbackTransformer('strtoupper');
$transform($element);
var_dump($element->getId());
var_dump($element->getData());
```

Outputs:
```php
string 'foo' (length=3)
string 'BAR' (length=3)
```

Otherwise, here's how we could achieve this by ourselves:
```php
use BenTools\ETL\Context\ContextElementInterface;
use BenTools\ETL\Transformer\TransformerInterface;

class MyTransformer implements TransformerInterface
{
    /**
     * @inheritDoc
     */
    public function __invoke(ContextElementInterface $element): void
    {
        $element->setData(strtoupper($element->getData()));
    }

}
```

Transformer Stack
-----------------

Provides a stack of transformers with priority management.

```php
require_once __DIR__ . '/vendor/autoload.php';

use BenTools\ETL\Context\ContextElement;
use BenTools\ETL\Context\ContextElementInterface;
use BenTools\ETL\Transformer\TransformerStack;

$transformer1 = function (ContextElementInterface $element) {
    $element->setData('foo');
};
$transformer2 = function (ContextElementInterface $element) {
    $element->setData('bar');
};
$transformer3 = function (ContextElementInterface $element) {
    if (1000 === $element->getId()) {
        $element->skip();
    }
};

$stack = new TransformerStack();
$stack->registerTransformer($transformer1, 100);
$stack->registerTransformer($transformer2, 50);
$stack->registerTransformer($transformer3, 75);

$element = new ContextElement(1000);

/**
 * This will execute in the following order:
 * - $transformer1
 * - $transformer3
 * - $transformer2
 */
$stack($element);

/**
 * Because $transformer3, executed in 2nd position, has set the element to be skipped,
 * $element data will not be set to 'bar'
 */
var_dump($element->getData()); // 'foo'
```


Step Transformer
----------------

You can also chain transformers with nameable steps. This can be useful to hook an additionnal transformer on a specific step of the transform workflow:
```php
require_once __DIR__ . '/vendor/autoload.php';

use BenTools\ETL\Context\ContextElement;
use BenTools\ETL\Context\ContextElementInterface;
use BenTools\ETL\Transformer\StepTransformer;

$stack = new StepTransformer([
    'step_1',
    'step_2'
]);

$stack->registerTransformer('step_1', function (ContextElementInterface $element) {
    $element->setData('foo');
});

$stack->registerTransformer('step_2', function (ContextElementInterface $element) {
    $element->setData('bar');
});

$stack->registerTransformer('step_2', function (ContextElementInterface $element) {
    $element->setData('baz');
}, 100); // Will be executed on top of step_2

$element = new ContextElement();
$stack($element);
var_dump($element->getData()); // 'bar'
```

Next: [Loaders](Loaders.md)