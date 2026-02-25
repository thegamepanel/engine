<?php
declare(strict_types=1);

namespace Engine\Container\Exceptions;

use Psr\Container\ContainerExceptionInterface;
use RuntimeException;
use Throwable;

final class InvalidMethodException extends RuntimeException implements ContainerExceptionInterface
{
    public static function make(string $class, string $method, ?Throwable $previous = null): self
    {
        return new self(sprintf(
            'The provided method %s::%s is not a valid method.',
            $class, $method
        ), previous: $previous);
    }
}
