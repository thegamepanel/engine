<?php
declare(strict_types=1);

namespace Engine\Container\Exceptions;

use Engine\Container\Contracts\ContainerException;
use RuntimeException;
use Throwable;

final class DependencyResolutionException extends RuntimeException implements ContainerException
{
    public static function cannotResolve(string $type, ?Throwable $previous = null): self
    {
        return new self(sprintf(
            'Cannot resolve a dependency of type "%s".', $type
        ), previous: $previous);
    }

    public static function intersection(string $type, ?Throwable $previous = null): self
    {
        return new self(sprintf(
            'Cannot resolve the intersection dependency "%s".', $type
        ), previous: $previous);
    }

    public static function intersectionNoBinding(string $type, ?Throwable $previous = null): self
    {
        return new self(sprintf(
            'Cannot resolve the intersection dependency "%s" without a binding.', $type
        ), previous: $previous);
    }

    public static function union(string $type, ?Throwable $previous = null): self
    {
        return new self(sprintf(
            'Cannot resolve the union dependency "%s".', $type
        ), previous: $previous);
    }

    public static function ghost(string $type): self
    {
        return new self(sprintf(
            'Cannot create a ghost object for "%s".', $type
        ));
    }
}
