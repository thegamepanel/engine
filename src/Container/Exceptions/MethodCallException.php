<?php
declare(strict_types=1);

namespace Engine\Container\Exceptions;

use Psr\Container\ContainerExceptionInterface;
use RuntimeException;
use Throwable;

final class MethodCallException extends RuntimeException implements ContainerExceptionInterface
{
    public static function make(string $class, string $method, ?Throwable $previous = null): self
    {
        return new self(sprintf(
            'Unable to call the provided method %s::%s.',
            $class, $method
        ), previous: $previous);
    }
}
