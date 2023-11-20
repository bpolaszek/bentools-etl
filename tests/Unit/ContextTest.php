<?php

declare(strict_types=1);

namespace BenTools\ETL\Tests\Unit;

use BenTools\ETL\EtlExecutor;

use function expect;
use function it;

it('provides a default context', function () {
    // Given
    $executor = (new EtlExecutor(context: ['color' => 'green', 'shape' => 'square', 'lights' => 'on']));

    // When
    $report = $executor->process([], context: ['shape' => 'round', 'size' => 'small']);

    // Then
    expect($report->context)->toBe(['color' => 'green', 'shape' => 'round', 'lights' => 'on', 'size' => 'small']);
});

it('adds some more context', function () {
    // Given
    $executor = (new EtlExecutor(context: ['color' => 'green', 'shape' => 'square', 'lights' => 'on']))
        ->withContext(['color' => 'blue', 'flavor' => 'vanilla']);

    // When
    $report = $executor->process([], context: ['shape' => 'round', 'size' => 'small']);

    // Then
    expect($report->context)->toBe(['color' => 'blue', 'shape' => 'round', 'lights' => 'on', 'flavor' => 'vanilla', 'size' => 'small']);
});

it('replaces the whole context', function () {
    // Given
    $executor = (new EtlExecutor(context: ['color' => 'green', 'shape' => 'square', 'lights' => 'on']))
        ->withContext(['color' => 'blue', 'flavor' => 'vanilla'], clear: true);

    // When
    $report = $executor->process([], context: ['shape' => 'round', 'size' => 'small']);

    // Then
    expect($report->context)->toBe(['color' => 'blue', 'flavor' => 'vanilla', 'shape' => 'round', 'size' => 'small']);
});

it('does not override existing values', function () {
    // Given
    $executor = (new EtlExecutor(context: ['color' => 'green', 'shape' => 'square', 'lights' => 'on']))
        ->withContext(['color' => 'blue', 'flavor' => 'vanilla'], overwrite: false);

    // When
    $report = $executor->process([], context: ['shape' => 'round', 'size' => 'small']);

    // Then
    expect($report->context)->toBe(['color' => 'green', 'shape' => 'round', 'lights' => 'on', 'flavor' => 'vanilla', 'size' => 'small']);
});
