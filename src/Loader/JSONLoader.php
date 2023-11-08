<?php

declare(strict_types=1);

namespace BenTools\ETL\Loader;

use BenTools\ETL\EtlState;
use BenTools\ETL\Exception\LoadException;
use SplFileObject;
use SplTempFileObject;

use function json_encode;
use function ltrim;
use function str_starts_with;
use function substr;
use function trim;

use const JSON_PRETTY_PRINT;
use const PHP_EOL;

final readonly class JSONLoader implements LoaderInterface
{
    public function __construct(
        public string|SplFileObject|null $destination = null,
    ) {
    }

    public function load(mixed $item, EtlState $state): void
    {
        $state->context[__CLASS__]['pending'][] = $item;
    }

    public function flush(bool $isPartial, EtlState $state): string
    {
        $context = &$state->context[__CLASS__];
        $context['hasStarted'] ??= false;
        $context['pending'] ??= [];

        $file = $context['file'] ??= $this->resolveDestination($state->destination ?? $this->destination);
        // $this->writeOpeningBracketIfNotDoneYet($state, $file);
        match ($isPartial) {
            true => $this->partialFlush($state, $file),
            false => $this->finalFlush($state, $file),
        };
        $context['pending'] = [];

        if (!$isPartial && $file instanceof SplTempFileObject) {
            $file->rewind();

            return implode('', [...$file]); // @phpstan-ignore-line
        }

        return 'file://'.$file->getPathname();
    }

    private function partialFlush(EtlState $state, SplFileObject $file): void
    {
        $context = &$state->context[__CLASS__];
        $serialized = json_encode($context['pending'], JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $serialized = ltrim($serialized, '[');
        $serialized = rtrim($serialized, ']');
        $serialized = trim($serialized);

        if (!($context['openingBracket'] ?? false)) {
            $file->fwrite('[');
            $context['openingBracket'] = true;
            $file->fwrite(PHP_EOL.'    '.$serialized);
        } elseif ([] !== $context['pending']) {
            $file->fwrite(',');
            $file->fwrite(PHP_EOL.'    '.$serialized);
        }
    }

    private function finalFlush(EtlState $state, SplFileObject $file): void
    {
        $this->partialFlush($state, $file);
        if ($state->nbLoadedItems > 0) {
            $file->fwrite(PHP_EOL);
        }
        $file->fwrite(']'.PHP_EOL);
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
