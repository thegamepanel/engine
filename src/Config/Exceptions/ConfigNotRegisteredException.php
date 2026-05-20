<?php
declare(strict_types=1);

namespace Engine\Config\Exceptions;

use Engine\Config\Contracts\ConfigException;
use RuntimeException;

final class ConfigNotRegisteredException extends RuntimeException implements ConfigException
{
    public static function forClass(string $class): self
    {
        return new self(sprintf(
            'No config has been registered for class "%s".',
            $class,
        ));
    }
}
