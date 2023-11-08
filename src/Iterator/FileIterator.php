<?php

declare(strict_types=1);

namespace BenTools\ETL\Iterator;

use IteratorAggregate;
use SplFileObject;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Traversable;

use function rtrim;

use const PHP_EOL;

/**
 * @implements IteratorAggregate<string>
 */
final readonly class FileIterator implements IteratorAggregate
{
    /**
     * @var array{skipEmptyLines: bool}
     */
    private array $options;

    /**
     * @param array{skipEmptyLines?: bool} $options
     */
    public function __construct(
        private SplFileObject $file,
        array $options = [],
    ) {
        $resolver = (new OptionsResolver())->setIgnoreUndefined();
        $resolver->setDefaults(['skipEmptyLines' => true]);
        $resolver->setAllowedTypes('skipEmptyLines', 'bool');
        $this->options = $resolver->resolve($options);
    }

    public function getIterator(): Traversable
    {
        foreach ($this->file as $row) {
            $line = rtrim($row, PHP_EOL);
            if ($this->options['skipEmptyLines'] && '' === $line) {
                continue;
            }
            yield $line;
        }
    }
}
