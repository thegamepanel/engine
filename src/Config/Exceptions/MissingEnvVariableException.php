<?php
declare(strict_types=1);

namespace Engine\Config\Exceptions;

use Engine\Config\Contracts\ConfigException;
use RuntimeException;

final class MissingEnvVariableException extends RuntimeException implements ConfigException
{
    public static function make(string $variable): self
    {
        return new self(sprintf(
            'The environment variable "%s" is missing.',
            $variable
        ));
    }
}
