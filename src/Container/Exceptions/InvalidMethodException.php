<?php
declare(strict_types=1);

namespace Engine\Container\Exceptions;

use Engine\Container\Contracts\ContainerException;
use RuntimeException;
use Throwable;

final class InvalidMethodException extends RuntimeException implements ContainerException
{
    public static function make(string $class, string $method, ?Throwable $previous = null): self
    {
        return new self(sprintf(
            'The provided method %s::%s is not a valid method.',
            $class, $method
        ), previous: $previous);
    }
}
