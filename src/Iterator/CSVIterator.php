<?php

declare(strict_types=1);

namespace BenTools\ETL\Iterator;

use BenTools\ETL\Normalizer\EmptyStringToNullNormalizer;
use BenTools\ETL\Normalizer\NumericStringToNumberNormalizer;
use BenTools\ETL\Normalizer\ValueNormalizerInterface;
use IteratorAggregate;
use SplFileObject;
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
     * @param Traversable<string>                                                                                                                            $text
     * @param array{delimiter?: string, enclosure?: string, escapeString?: string, columns?: 'auto'|string[]|null, normalizers?: ValueNormalizerInterface[]} $options
     */
    public function __construct(
        private Traversable $text,
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

    /**
     * @param array<int|string, mixed> $data
     * @param list<string>|null        $columns
     *
     * @return array|string[]
     */
    private function extract(array $data, ?array $columns): array
    {
        if ($this->options['normalizers']) {
            array_walk($data, function (&$value) {
                foreach ($this->options['normalizers'] as $normalizer) {
                    $value = $normalizer->normalize($value);
                }

                return $value;
            });
        }

        return !empty($columns) ? self::combine($columns, $data) : $data;
    }

    public function getIterator(): Traversable
    {
        if ($this->text instanceof SplFileObject) {
            return $this->iterateFromFile($this->text);
        }

        return $this->iterateFromContent($this->text);
    }

    /**
     * @return Traversable<mixed>
     */
    private function iterateFromFile(SplFileObject $file): Traversable
    {
        $flags = [SplFileObject::READ_CSV, $file->getFlags()];
        $file->setFlags(array_reduce($flags, fn ($a, $b) => $a | $b, 0));
        $columns = $this->options['columns'];
        if ('auto' === $columns) {
            $columns = null;
        }
        while (!$file->eof()) {
            $fields = $file->fgetcsv(
                $this->options['delimiter'],
                $this->options['enclosure'],
                $this->options['escapeString'],
            );
            if ([null] === $fields) {
                continue;
            }
            if ('auto' === $this->options['columns'] && 0 === $file->key()) {
                $columns ??= $fields;
                continue;
            }

            yield $this->extract($fields, $columns);
        }
    }

    /**
     * @param Traversable<string> $content
     *
     * @return Traversable<mixed>
     */
    private function iterateFromContent(Traversable $content): Traversable
    {
        $columns = $this->options['columns'];
        if ('auto' === $columns) {
            $columns = null;
        }
        foreach ($content as $r => $row) {
            $fields = str_getcsv(
                $row,
                $this->options['delimiter'],
                $this->options['enclosure'],
                $this->options['escapeString'],
            );
            if ('auto' === $this->options['columns'] && 0 === $r) {
                $columns ??= $fields;
                continue;
            }
            yield $this->extract($fields, $columns);
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
