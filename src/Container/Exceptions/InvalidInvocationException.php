<?php
declare(strict_types=1);

namespace Engine\Container\Exceptions;

use Engine\Container\Contracts\ContainerException;
use InvalidArgumentException;

final class InvalidInvocationException extends InvalidArgumentException implements ContainerException
{
    public static function notPublic(string $class, string $method): self
    {
        return new self(
            sprintf('Method %s::%s is not public.', $class, $method)
        );
    }

    public static function alreadyInitialised(string $class): self
    {
        return new self(
            sprintf('Cannot call the constructor of %s because it is already initialised.', $class)
        );
    }
}
