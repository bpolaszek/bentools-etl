# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

`bentools/etl` is a PHP library implementing the Extract/Transform/Load pattern for data processing workflows. It's designed to be flexible, event-driven, and support both synchronous and asynchronous (ReactPHP) processing.

**Core concept:** Extract data from a source, apply transformations, and load results into a destination.

## Commands

### Testing & Quality
```bash
# Run all CI checks (PHP-CS-Fixer, PHPStan, Pest with coverage)
composer ci:check

# Run tests only
vendor/bin/pest

# Run tests with coverage
vendor/bin/pest --coverage

# Run a single test file
vendor/bin/pest tests/Behavior/FlushTest.php

# Run PHPStan type checking
vendor/bin/phpstan analyse

# Run code style fixer
vendor/bin/php-cs-fixer fix
```

### Requirements
- PHP >=8.2
- Tests use Pest (not PHPUnit syntax)
- 100% code coverage expected before PRs

## Architecture

### Core Components

**EtlExecutor** (`src/EtlExecutor.php`)
- Main entry point for building and executing ETL workflows
- Uses builder pattern via `EtlBuilderTrait` to chain extractors, transformers, and loaders
- Dispatches events at each lifecycle stage (init, extract, transform, load, flush, end)
- Handles exceptions through dedicated event types (ExtractException, TransformException, etc.)

**EtlState** (`src/EtlState.php`)
- Immutable state object passed through the entire workflow
- Tracks: current item, indices, flush timing, loaded items count, output
- Contains context (arbitrary data), source, and destination
- Version system for state updates during processing

**EtlConfiguration** (`src/EtlConfiguration.php`)
- Configuration object for flush frequency, batch size, and other options
- `flushEvery` - Controls how often the loader flushes (default: INF)
- `batchSize` - Controls how many items are grouped for batch transformation (default: 1)

### Three Main Interfaces

1. **ExtractorInterface** (`src/Extractor/`)
   - `extract(EtlState $state): iterable` - Returns an iterable of items to process
   - Built-in: CSV, JSON, FileExtractor, STDINExtractor, IterableExtractor, ReactStreamExtractor

2. **TransformerInterface** (`src/Transformer/`)
   - `transform(mixed $item, EtlState $state): mixed` - Transforms extracted items
   - Return value can be a single value, an array, or a generator (yield)
   - Yielded items generate multiple loads from a single extracted item
   - Built-in: CallableTransformer, ChainTransformer, NullTransformer

3. **BatchTransformerInterface** (`src/Transformer/`)
   - `transform(array $items, EtlState $state): Generator` - Transforms a batch of items at once
   - Separate interface from `TransformerInterface` (does NOT extend it)
   - Activated when `batchSize` is set in `EtlConfiguration` and transformer implements this interface
   - Each yielded value becomes an individual item for the load phase
   - Built-in: CallableBatchTransformer

4. **LoaderInterface** (`src/Loader/`)
   - `load(mixed $item, EtlState $state): void` - Loads transformed items
   - `flush(bool $isEarly, EtlState $state): mixed` - Called at flush frequency or end
   - Built-in: InMemoryLoader, CSV, JSON, DoctrineORM, STDOUTLoader

### Event System

**Event dispatching** (`src/EventDispatcher/`)
- Custom PSR-14 implementation with priority support
- Events: InitEvent, StartEvent, ExtractEvent, TransformEvent, BeforeLoadEvent, LoadEvent, FlushEvent, EndEvent
- Exception events: ExtractExceptionEvent, TransformExceptionEvent, LoadExceptionEvent, FlushExceptionEvent
- Use `->on{EventName}(callable $listener, int $priority = 0)` on EtlExecutor

**Control flow exceptions:**
- `SkipRequest` - Skip current item, continue processing
- `StopRequest` - Stop entire workflow immediately

### Processors

**ProcessorInterface** (`src/Processor/`)
- `IterableProcessor` - Default synchronous processing
- `ReactStreamProcessor` - Async processing with ReactPHP streams (experimental)

### Recipes

**Recipe** (`src/Recipe/`)
- Reusable workflow configurations (combine extractors, transformers, loaders, event listeners)
- `FilterRecipe` - Skip/exclude items based on callable filter
- `LoggerRecipe` - PSR-3 logging integration

### Utility Functions

`src/functions.php` provides helper functions:
- `extractFrom()` - Create executor starting with extractor
- `transformWith()` - Create executor starting with transformer
- `loadInto()` - Create executor starting with loader
- `withRecipe()` - Create executor with recipe
- `chain()` - Chain multiple extractors/transformers/loaders
- `stdIn()` / `stdOut()` - STDIN/STDOUT helpers
- `skipWhen()` - Conditional skip recipe

## Key Patterns

### Immutability & Cloning
- EtlExecutor uses `ClonableTrait` - all builder methods return clones
- EtlState has version tracking - always get latest via `$state->getLastVersion()`

### Fluent Building
```php
$executor = (new EtlExecutor())
    ->extractFrom($extractor)
    ->transformWith($transformer)
    ->loadInto($loader)
    ->onTransform(fn($event) => /* ... */)
    ->process($source, $destination);
```

### NextTick Callbacks
- `$state->nextTick(callable $callback)` - Schedule callback after current item
- Useful for deferring operations or cleanup
- Consumed between items and guaranteed to run even if workflow stops

### Batch Transform
- Configure via `new EtlConfiguration(batchSize: N)` to group N items per batch
- Requires a transformer implementing `BatchTransformerInterface` (separate from `TransformerInterface`)
- Processing flow: items are chunked via `iterable_chunk()`, then for each chunk:
  1. ExtractEvent fires per item (items can be skipped individually)
  2. `transform(array $items, EtlState $state): Generator` is called once for the whole batch
  3. Each yielded result goes through TransformEvent → Load individually
- `nextTick` callbacks are consumed between batches, not between items within a batch
- When `batchSize` is set but transformer is not `BatchTransformerInterface`, batching is ignored
- Note: `$state->currentItemKey` during Transform/Load events points to the last item of the batch

### Flush Timing
- Configurable via `new EtlConfiguration(flushEvery: N)`
- `flush()` called when: frequency threshold reached, or at end (with `$isEarly = false`)
- Early flush = during processing, final flush = at termination

## Testing Patterns

- Tests are organized in `tests/Behavior/` and `tests/Unit/`
- Use Pest syntax (`test()`, `expect()`, `it()`)
- Mock with Mockery when needed
- Coverage is tracked - don't reduce it

## Development Notes

- PHP 8.2+ features are welcome (readonly properties, enums, etc.)
- Prefer immutability and value objects
- Event listeners should be side-effect free when possible
- Transformers returning generators (yield) allow 1-to-many transformations
- Loaders can implement `ConditionalLoaderInterface` to skip certain items
