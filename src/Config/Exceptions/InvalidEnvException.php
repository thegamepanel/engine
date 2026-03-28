<?php
declare(strict_types=1);

namespace Engine\Config\Exceptions;

use Engine\Config\Contracts\ConfigException;
use RuntimeException;

final class InvalidEnvException extends RuntimeException implements ConfigException
{
    public static function make(string $variable, string $type): self
    {
        return new self(sprintf(
            'The environment variable "%s" must be of type %s, or castable to it.',
            $variable,
            $type,
        ));
    }
}
