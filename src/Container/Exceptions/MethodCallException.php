<?php
declare(strict_types=1);

namespace Engine\Container\Exceptions;

use Engine\Container\Contracts\ContainerException;
use RuntimeException;
use Throwable;

final class MethodCallException extends RuntimeException implements ContainerException
{
    public static function make(string $class, string $method, ?Throwable $previous = null): self
    {
        return new self(sprintf(
            'Unable to call the provided method %s::%s.',
            $class, $method
        ), previous: $previous);
    }
}
