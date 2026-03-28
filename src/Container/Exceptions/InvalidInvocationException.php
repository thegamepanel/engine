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

    public static function notCallable(): self
    {
        return new self('Cannot invoke a non-callable.');
    }

    public static function notMethod(): self
    {
        return new self('Cannot invoke a non-string method.');
    }
}
