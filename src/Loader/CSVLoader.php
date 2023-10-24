<?php

declare(strict_types=1);

namespace Bentools\ETL\Loader;

use Bentools\ETL\EtlState;
use Bentools\ETL\Exception\LoadException;
use SplFileObject;
use SplTempFileObject;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function array_keys;
use function is_array;
use function str_starts_with;
use function substr;

use const PHP_EOL;

final readonly class CSVLoader implements LoaderInterface
{
    /**
     * @var array{delimiter: string, enclosure: string, escapeString: string, columns: 'auto'|string[]|null, eol: string}
     */
    public array $options;

    /**
     * @param array{delimiter?: string, enclosure?: string, escapeString?: string, columns?: 'auto'|string[]|null, eol?: string} $options
     */
    public function __construct(
        public string|SplFileObject|null $destination = null,
        array $options = [],
    ) {
        $resolver = (new OptionsResolver())->setIgnoreUndefined();
        $resolver->setDefaults([
            'delimiter' => ',',
            'enclosure' => '"',
            'escapeString' => '\\',
            'columns' => null,
            'eol' => PHP_EOL,
        ]);
        $resolver->setAllowedTypes('delimiter', 'string');
        $resolver->setAllowedTypes('enclosure', 'string');
        $resolver->setAllowedTypes('escapeString', 'string');
        $resolver->setAllowedTypes('columns', ['string[]', 'null', 'string']);
        $resolver->setAllowedValues('columns', function (array|string|null $value) {
            return 'auto' === $value || null === $value || is_array($value);
        });
        $resolver->setAllowedTypes('eol', 'string');
        $this->options = $resolver->resolve($options);
    }

    public function load(mixed $item, EtlState $state): void
    {
        $context = &$state->context[__CLASS__];
        $context['columsWritten'] ??= false;

        if (!$context['columsWritten']) {
            if (is_array($this->options['columns'])) {
                $context['pending'][] = $this->options['columns'];
                $context['columsWritten'] = true;
            } elseif ('auto' === $this->options['columns']) {
                $context['pending'][] = array_keys($item);
                $context['columsWritten'] = true;
            }
        }

        $context['pending'][] = $item;
    }

    public function flush(bool $isPartial, EtlState $state): string
    {
        $context = &$state->context[__CLASS__];
        $context['pending'] ??= [];
        $file = $context['file'] ??= $this->resolveDestination($state->destination ?? $this->destination);
        foreach ($context['pending'] as $item) {
            $this->write($file, $item);
        }

        $context['pending'] = [];

        if (!$isPartial && $file instanceof SplTempFileObject) {
            $file->rewind();

            return implode('', [...$file]); // @phpstan-ignore-line
        }

        return 'file://'.$file->getPathname();
    }

    /**
     * @param array<int|string, string> $item
     */
    private function write(SplFileObject $file, array $item): void
    {
        $options = $this->options;
        $file->fputcsv($item, $options['delimiter'], $options['enclosure'], $options['escapeString'], $options['eol']);
    }

    private function resolveDestination(mixed $destination): SplFileObject
    {
        $isFileName = is_string($destination) && str_starts_with($destination, 'file://');

        return match (true) {
            $destination instanceof SplFileObject => $destination,
            $isFileName => new SplFileObject(substr($destination, 7), 'w'),
            null === $destination => new SplTempFileObject(),
            default => throw new LoadException('Invalid destination.'),
        };
    }
}
