<?php
declare(strict_types=1);

namespace Engine\Config\Exceptions;

use Engine\Config\Contracts\ConfigException;
use RuntimeException;

final class EnvInitialisationException extends RuntimeException implements ConfigException
{
    public static function notInitialised(): self
    {
        return new self('The Env class has not been initialised.');
    }

    public static function alreadyInitialised(): self
    {
        return new self('The Env class has already been initialised.');
    }
}
