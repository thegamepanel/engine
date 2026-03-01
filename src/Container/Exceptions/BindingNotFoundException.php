<?php
declare(strict_types=1);

namespace Engine\Container\Exceptions;

use Engine\Container\Contracts\ContainerException;
use RuntimeException;

final class BindingNotFoundException extends RuntimeException implements ContainerException
{
    public static function class(string $class): self
    {
        return new self(
            sprintf('No binding found for %s', $class)
        );
    }

    public static function named(string $class, string $name): self
    {
        return new self(
            sprintf('No binding found for %s with name %s', $class, $name)
        );
    }

    public static function qualified(string $class, string $qualifier): self
    {
        return new self(
            sprintf('No binding found for %s qualified by %s', $class, $qualifier)
        );
    }
}
