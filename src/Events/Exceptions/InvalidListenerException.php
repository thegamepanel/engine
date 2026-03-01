<?php
declare(strict_types=1);

namespace Engine\Events\Exceptions;

use Engine\Events\Contracts\EventException;
use InvalidArgumentException;

final class InvalidListenerException extends InvalidArgumentException implements EventException
{
    public static function wrongParameterCount(string $class, string $method): self
    {
        return new self(sprintf(
            'Event listener %s::%s must have exactly one parameter.', $class, $method
        ));
    }

    public static function invalidEventClass(string $class, string $method): self
    {
        return new self(sprintf(
            'Event listener %s::%s must use a valid event class as the parameter type.', $class, $method
        ));
    }

    public static function notStatic(string $class, string $method): self
    {
        return new self(sprintf(
            'Event listener %s::%s must be static when the subscriber is not an object.', $class, $method
        ));
    }
}
