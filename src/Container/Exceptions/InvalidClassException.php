<?php
declare(strict_types=1);

namespace Engine\Container\Exceptions;

use Engine\Container\Contracts\ContainerException;
use RuntimeException;
use Throwable;

final class InvalidClassException extends RuntimeException implements ContainerException
{
    public static function make(string $class, ?Throwable $previous = null): self
    {
        return new self(sprintf(
            'The provided class %s is not a valid class.',
            $class
        ), previous: $previous);
    }
}
