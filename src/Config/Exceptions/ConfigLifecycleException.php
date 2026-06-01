<?php
declare(strict_types=1);

namespace Engine\Config\Exceptions;

use Engine\Config\Contracts\ConfigException;
use LogicException;

final class ConfigLifecycleException extends LogicException implements ConfigException
{
    public static function coreAlreadySealed(): self
    {
        return new self('The core config has already been sealed.');
    }

    public static function alreadySealed(): self
    {
        return new self('The config registry has already been sealed.');
    }

    public static function sealBeforeCoreSeal(): self
    {
        return new self('seal() cannot be called before sealCore().');
    }

    public static function registerBeforeCoreSeal(): self
    {
        return new self('Module configs cannot be registered before sealCore() has been called.');
    }

    public static function registerAfterSeal(): self
    {
        return new self('Module configs cannot be registered after the registry has been sealed.');
    }

    public static function registerAsCore(string $module): self
    {
        return new self(sprintf(
            'The module name "%s" is reserved for core configs and cannot be used by register().',
            $module,
        ));
    }

    public static function dottedModuleOrName(string $module, string $name): self
    {
        return new self(sprintf(
            'Module and config names must not contain dots; got module "%s", name "%s".',
            $module,
            $name,
        ));
    }

    public static function readBeforeCoreSeal(): self
    {
        return new self('Configs cannot be read from the registry before sealCore() has been called.');
    }

    public static function readAfterSeal(): self
    {
        return new self(
            'Configs cannot be read from the registry after seal() has been called. '
            . 'Use the returned ConfigCatalogue.',
        );
    }
}
