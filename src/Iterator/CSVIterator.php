<?php

declare(strict_types=1);

namespace Bentools\ETL\Iterator;

use Bentools\ETL\Normalizer\EmptyStringToNullNormalizer;
use Bentools\ETL\Normalizer\NumericStringToNumberNormalizer;
use Bentools\ETL\Normalizer\ValueNormalizerInterface;
use IteratorAggregate;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Traversable;

use function array_combine;
use function array_fill;
use function array_merge;
use function array_slice;
use function array_values;
use function array_walk;
use function count;
use function is_array;
use function str_getcsv;

/**
 * @implements IteratorAggregate<array<mixed>>
 */
final readonly class CSVIterator implements IteratorAggregate
{
    /**
     * @var array{delimiter: string, enclosure: string, escapeString: string, columns: 'auto'|string[]|null, normalizers: ValueNormalizerInterface[]}
     */
    private array $options;

    /**
     * @param array{delimiter?: string, enclosure?: string, escapeString?: string, columns?: 'auto'|string[]|null, normalizers?: ValueNormalizerInterface[]} $options
     */
    public function __construct(
        private PregSplitIterator|StrTokIterator|FileIterator $text,
        array $options = [],
    ) {
        $resolver = (new OptionsResolver())->setIgnoreUndefined();
        $resolver->setDefaults([
            'delimiter' => ',',
            'enclosure' => '"',
            'escapeString' => '\\',
            'columns' => null,
            'normalizers' => [
                new NumericStringToNumberNormalizer(),
                new EmptyStringToNullNormalizer(),
            ],
        ]);
        $resolver->setAllowedTypes('delimiter', 'string');
        $resolver->setAllowedTypes('enclosure', 'string');
        $resolver->setAllowedTypes('escapeString', 'string');
        $resolver->setAllowedTypes('normalizers', ValueNormalizerInterface::class.'[]');
        $resolver->setAllowedTypes('columns', ['string[]', 'null', 'string']);
        $resolver->setAllowedValues('columns', function (array|string|null $value) {
            return 'auto' === $value || null === $value || is_array($value);
        });
        $this->options = $resolver->resolve($options);
    }

    public function getIterator(): Traversable
    {
        $columns = $this->options['columns'];
        if ('auto' === $columns) {
            $columns = null;
        }
        foreach ($this->text as $r => $row) {
            $fields = str_getcsv(
                $row,
                $this->options['delimiter'],
                $this->options['enclosure'],
                $this->options['escapeString'],
            );
            if (0 === $r && 'auto' === $this->options['columns']) {
                $columns ??= $fields;
                continue;
            }

            if ($this->options['normalizers']) {
                array_walk($fields, function (&$value) {
                    foreach ($this->options['normalizers'] as $normalizer) {
                        $value = $normalizer->normalize($value);
                    }

                    return $value;
                });
            }

            if (!empty($columns)) {
                yield self::combine($columns, $fields);
                continue;
            }
            yield $fields;
        }
    }

    /**
     * @param string[] $keys
     * @param string[] $values
     *
     * @return string[]
     */
    private static function combine(array $keys, array $values): array
    {
        $nbKeys = count($keys);
        $nbValues = count($values);

        if ($nbKeys < $nbValues) {
            return array_combine($keys, array_slice(array_values($values), 0, $nbKeys));
        }

        if ($nbKeys > $nbValues) {
            return array_combine($keys, array_merge($values, array_fill(0, $nbKeys - $nbValues, null)));
        }

        return array_combine($keys, $values);
    }
}
