<?php
declare(strict_types=1);

namespace Engine\Container\Exceptions;

use Engine\Container\Contracts\ContainerException;
use RuntimeException;
use Throwable;

final class InvalidFunctionException extends RuntimeException implements ContainerException
{
    public static function make(string $function, ?Throwable $previous = null): self
    {
        return new self(sprintf(
            'The provided function %s does not exist.',
            $function
        ), previous: $previous);
    }
}
