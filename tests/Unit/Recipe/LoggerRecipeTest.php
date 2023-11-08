<?php

declare(strict_types=1);

namespace BenTools\ETL\Tests\Unit\Recipe;

use Bentools\ETL\EtlConfiguration;
use Bentools\ETL\EtlExecutor;
use Bentools\ETL\Recipe\LoggerRecipe;
use Monolog\Handler\TestHandler;
use Monolog\Level;
use Monolog\Logger;

it('logs messages during ETL process', function () {
    // Given
    $handler = new TestHandler();
    $logger = new Logger('test', [$handler]);
    $loggerRecipe = new LoggerRecipe($logger);
    $executor = new EtlExecutor(options: new EtlConfiguration(flushEvery: 1));
    $executor = $executor->withRecipe($loggerRecipe);

    // When
    $executor->process(['foo', 'bar']);

    // Then
    $records = $handler->getRecords();
    expect($records)->toHaveCount(12)->and($records)->sequence(
        fn ($record) => $record->message->toEqual('Initializing ETL...')->and($record->level->toBe(Level::Debug)),
        fn ($record) => $record->message->toEqual('Starting ETL...')->and($record->level->toBe(Level::Info)),
        fn ($record) => $record->message->toContain('Extracting item')->and($record->level->toBe(Level::Debug)),
        fn ($record) => $record->message->toContain('Transformed item')->and($record->level->toBe(Level::Debug)),
        fn ($record) => $record->message->toContain('Loaded item')->and($record->level->toBe(Level::Debug)),
        fn ($record) => $record->message->toContain('Flushing items (partial)...')->and($record->level->toBe(Level::Info)),
        fn ($record) => $record->message->toContain('Extracting item')->and($record->level->toBe(Level::Debug)),
        fn ($record) => $record->message->toContain('Transformed item')->and($record->level->toBe(Level::Debug)),
        fn ($record) => $record->message->toContain('Loaded item')->and($record->level->toBe(Level::Debug)),
        fn ($record) => $record->message->toContain('Flushing items (partial)...')->and($record->level->toBe(Level::Info)),
        fn ($record) => $record->message->toContain('Flushing items...')->and($record->level->toBe(Level::Info)),
        fn ($record) => $record->message->toContain('ETL complete.')->and($record->level->toBe(Level::Info)),
    );
});
