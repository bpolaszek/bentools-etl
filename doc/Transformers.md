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

Next: [Loaders](Loaders.md)