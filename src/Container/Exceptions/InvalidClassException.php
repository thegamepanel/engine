<?php
declare(strict_types=1);

namespace Engine\Container\Exceptions;

use Psr\Container\ContainerExceptionInterface;
use RuntimeException;
use Throwable;

final class InvalidClassException extends RuntimeException implements ContainerExceptionInterface
{
    public static function make(string $class, ?Throwable $previous = null): self
    {
        return new self(sprintf(
            'The provided class %s is not a valid class.',
            $class
        ), previous: $previous);
    }
}
