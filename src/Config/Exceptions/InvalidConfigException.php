<?php
declare(strict_types=1);

namespace Engine\Config\Exceptions;

use Engine\Config\Contracts\ConfigException;
use RuntimeException;
use Throwable;

final class InvalidConfigException extends RuntimeException implements ConfigException
{
    public static function hydrationFailed(
        string     $file,
        string     $section,
        string     $message,
        ?string    $key = null,
        ?Throwable $previous = null,
    ): self {
        $location = $key !== null ? $section . '.' . $key : $section;

        return new self(
            sprintf('Invalid config in %s at [%s]: %s', $file, $location, $message),
            previous: $previous,
        );
    }

    public static function envVarMissing(string $variable, string $path): self
    {
        return new self(sprintf(
            'Required environment variable "%s" is not set at [%s].',
            $variable,
            $path,
        ));
    }

    public static function reservedKey(string $key, string $file): self
    {
        return new self(sprintf(
            'The top-level key "%s" is reserved by the loader and may not be declared in %s.',
            $key,
            $file,
        ));
    }

    public static function tomlParseError(string $file, Throwable $previous): self
    {
        return new self(
            sprintf('Failed to parse TOML file %s: %s', $file, $previous->getMessage()),
            previous: $previous,
        );
    }

    public static function fileReadError(string $file, string $reason): self
    {
        return new self(sprintf(
            'Failed to read TOML file %s: %s',
            $file,
            $reason,
        ));
    }
}
