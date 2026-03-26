<?php
declare(strict_types=1);

namespace Engine\Container\Exceptions;

use Engine\Container\Attributes\NoResolution;
use Engine\Container\Contracts\ContainerException;
use InvalidArgumentException;

class UnresolvableClassException extends InvalidArgumentException implements ContainerException
{
    public static function make(string $class): self
    {
        return new self(sprintf(
            'The class %s is marked with \'%s\', so cannot be resolved automatically.',
            $class, NoResolution::class
        ));
    }
}
