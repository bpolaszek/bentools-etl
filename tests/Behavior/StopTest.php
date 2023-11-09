<?php

declare(strict_types=1);

namespace BenTools\ETL\Tests\Behavior;

use BenTools\ETL\EtlExecutor;
use BenTools\ETL\EventDispatcher\Event\ExtractEvent;
use BenTools\ETL\EventDispatcher\Event\LoadEvent;
use BenTools\ETL\EventDispatcher\Event\TransformEvent;
use BenTools\ETL\Extractor\CSVExtractor;

use function dirname;
use function expect;

it('stops the process during extraction', function () {
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
            if ('JP' === $event->item['country_iso_code']) {
                $event->state->stop();
            }
        });

    // When
    $executor->process();

    // Then
    expect($cities)->toBe([
        'New York',
        'Los Angeles',
    ]);
});

it('stops the process during transformation', function () {
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
            if ('Shanghai' === [...$event->transformResult][0]) {
                $event->state->stop();
            }
        });

    // When
    $executor->process();

    // Then
    expect($cities)->toBe([
        'New York',
        'Los Angeles',
        'Tokyo',
    ]);
});

it('stops the process during loading', function () {
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
        ->onLoad(function (LoadEvent $event) {
            if ('Shanghai' === $event->item) {
                $event->state->stop();
            }
        });

    // When
    $executor->process();

    // Then
    expect($cities)->toBe([
        'New York',
        'Los Angeles',
        'Tokyo',
        'Shanghai',
    ]);
});
