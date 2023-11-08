<?php

declare(strict_types=1);

namespace BenTools\ETL\Tests\Behavior;

use BenTools\ETL\EtlExecutor;
use BenTools\ETL\EventDispatcher\Event\ExtractEvent;
use BenTools\ETL\EventDispatcher\Event\TransformEvent;
use BenTools\ETL\Extractor\CSVExtractor;

use function dirname;
use function expect;

it('skips items during extraction', function () {
    $extractor = new CSVExtractor('file://'.dirname(__DIR__).'/data/10-biggest-cities.csv', [
        'columns' => 'auto',
    ]);
    $cities = [];

    // Given
    $executor = (new EtlExecutor(extractor: $extractor))
        ->transformWith(function (mixed $value) {
            yield $value['city_english_name'];
        })
        ->loadInto(function (string $city) use (&$cities) {
            $cities[] = $city;
        })
        ->onExtract(function (ExtractEvent $event) {
            if ('US' === $event->item['country_iso_code']) {
                $event->state->skip();
            }
        });

    // When
    $executor->process();

    // Then
    expect($cities)->toBe([
        'Tokyo',
        'Shanghai',
        'Mumbai',
        'Istanbul',
        'Moscow',
        'Cairo',
        'Lima',
        'London',
    ]);
});

it('skips items during transformation', function () {
    $extractor = new CSVExtractor('file://'.dirname(__DIR__).'/data/10-biggest-cities.csv', [
        'columns' => 'auto',
    ]);
    $cities = [];

    // Given
    $executor = (new EtlExecutor(extractor: $extractor))
        ->transformWith(function (mixed $value) {
            yield $value['city_english_name'];
        })
        ->loadInto(function (string $city) use (&$cities) {
            $cities[] = $city;
        })
        ->onTransform(function (TransformEvent $event) {
            if ('Tokyo' === [...$event->items][0]) {
                $event->state->skip();
            }
        });

    // When
    $executor->process();

    // Then
    expect($cities)->toBe([
        'New York',
        'Los Angeles',
        'Shanghai',
        'Mumbai',
        'Istanbul',
        'Moscow',
        'Cairo',
        'Lima',
        'London',
    ]);
});
