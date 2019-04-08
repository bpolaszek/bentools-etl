<?php

namespace BenTools\ETL\Exception;

use Throwable;

final class UnexpectedTypeException extends \InvalidArgumentException
{
    /**
     * @inheritDoc
     */
    public function __construct(string $expectedType, $actualObject, Throwable $previous = null)
    {
        if (is_a($expectedType, $expectedType, true)) {
            $message = sprintf('Expected instance of %s, %s given.', $expectedType, \is_object($actualObject) ? \get_class($actualObject) : \gettype($actualObject));
        } else {
            $message = sprintf('Expected %s, %s given.', $expectedType, \is_object($actualObject) ? \get_class($actualObject) : \gettype($actualObject));
        }
        parent::__construct($message, 0, $previous);
    }

    /**
     * Throw an UnexpectedTypeException if actual object doesn't match expected type.
     *
     * @param        $actualObject
     * @param string $expectedType
     * @param bool   $allowNull
     * @throws UnexpectedTypeException
     */
    public static function throwIfNot($actualObject, string $expectedType, bool $allowNull = false): void
    {
        if (!is_a($expectedType, $expectedType, true)) {
            if (\gettype($actualObject) !== $expectedType) {
                if (null === $actualObject && $allowNull) {
                    return;
                }
                throw new self($expectedType, $actualObject);
            }
            return;
        }

        if (!$actualObject instanceof $expectedType) {
            if (null === $actualObject && $allowNull) {
                return;
            }
            throw new self($expectedType, $actualObject);
        }
    }
}
