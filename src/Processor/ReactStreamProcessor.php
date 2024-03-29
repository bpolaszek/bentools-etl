<?php

declare(strict_types=1);

namespace BenTools\ETL\Processor;

use BenTools\ETL\EtlExecutor;
use BenTools\ETL\EtlState;
use BenTools\ETL\Exception\ExtractException;
use BenTools\ETL\Exception\SkipRequest;
use BenTools\ETL\Exception\StopRequest;
use BenTools\ETL\Extractor\ReactStreamExtractor;
use BenTools\ETL\Recipe\Recipe;
use React\EventLoop\Loop;
use React\Stream\ReadableStreamInterface;
use Throwable;

use function is_string;
use function trim;

/**
 * @experimental
 */
final class ReactStreamProcessor extends Recipe implements ProcessorInterface
{
    public function supports(mixed $extracted): bool
    {
        return $extracted instanceof ReadableStreamInterface;
    }

    /**
     * @param ReadableStreamInterface $stream
     */
    public function process(EtlExecutor $executor, EtlState $state, mixed $stream): EtlState
    {
        $key = -1;
        $stream->on('data', function (mixed $item) use ($executor, &$key, $state, $stream) {
            if (is_string($item)) {
                $item = trim($item);
            }
            try {
                $executor->processItem($item, ++$key, $state);
            } catch (SkipRequest) {
            } catch (StopRequest) {
                $stream->close();
            } catch (Throwable $e) {
                $stream->close();
                ExtractException::emit($executor, $e, $state);
            }
        });

        Loop::run();

        return $state->getLastVersion();
    }

    public function decorate(EtlExecutor $executor): EtlExecutor
    {
        return $executor->extractFrom(new ReactStreamExtractor())->withProcessor($this);
    }
}
