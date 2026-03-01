<?php
declare(strict_types=1);

namespace Engine\Container\Exceptions;

use Engine\Container\Contracts\ContainerException;
use InvalidArgumentException;

final class InvalidResolverException extends InvalidArgumentException implements ContainerException
{
    public static function resolvable(string $class): self
    {
        return new self(
            sprintf('"%s" is not a valid resolvable.', $class)
        );
    }

    public static function resolver(string $class): self
    {
        return new self(
            sprintf('"%s" is not a valid resolver.', $class)
        );
    }

    public static function noDefault(): self
    {
        return new self('There is no default resolver.');
    }
}
