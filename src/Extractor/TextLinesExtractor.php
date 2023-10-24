<?php

declare(strict_types=1);

namespace Bentools\ETL\Extractor;

use Bentools\ETL\EtlState;
use Bentools\ETL\Iterator\PregSplitIterator;
use Bentools\ETL\Iterator\StrTokIterator;
use EmptyIterator;
use Symfony\Component\OptionsResolver\OptionsResolver;

final readonly class TextLinesExtractor implements ExtractorInterface
{
    /**
     * @var array{skipEmptyLines: bool}
     */
    private array $options;

    /**
     * @param array{skipEmptyLines?: bool} $options
     */
    public function __construct(
        private ?string $content = null,
        array $options = [],
    ) {
        $resolver = new OptionsResolver();
        $resolver->setIgnoreUndefined();
        $resolver->setDefaults(['skipEmptyLines' => true]);
        $resolver->setAllowedTypes('skipEmptyLines', 'bool');
        $this->options = $resolver->resolve($options);
    }

    public function extract(EtlState $state): StrTokIterator|PregSplitIterator|EmptyIterator
    {
        $content = $state->source ?? $this->content;

        if (null === $content) {
            return new EmptyIterator();
        }

        if ($this->options['skipEmptyLines']) {
            return new StrTokIterator($content);
        }

        return new PregSplitIterator($content);
    }
}
