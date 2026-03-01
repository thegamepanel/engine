<?php
declare(strict_types=1);

namespace Engine\Modules\Exceptions;

use Engine\Modules\Attributes\Manifest;
use Engine\Modules\Attributes\Registrar;
use Engine\Modules\Contracts\ModuleException;
use Engine\Modules\ModuleManifest;
use Engine\Modules\ModuleRegistrar;
use RuntimeException;

class ModuleResolutionException extends RuntimeException implements ModuleException
{
    public static function registrarResolver(): self
    {
        return new self(sprintf(
            'The module registrar resolver can only resolve parameters using the "%s" attribute.', Registrar::class
        ));
    }

    public static function notRegistrarType(): self
    {
        return new self(sprintf(
            'The module registrar resolver can only resolve parameters of type "%s".', ModuleRegistrar::class
        ));
    }

    public static function unresolvableRegistrar(string $ident): self
    {
        return new self(sprintf(
            'Cannot resolve the module registrar for module "%s".', $ident
        ));
    }

    public static function manifestResolver(): self
    {
        return new self(sprintf(
            'The module manifest resolver can only resolve parameters using the "%s" attribute.', Manifest::class
        ));
    }

    public static function notManifestType(): self
    {
        return new self(sprintf(
            'The module manifest resolver can only resolve parameters of type "%s".', ModuleManifest::class
        ));
    }

    public static function unresolvableManifest(string $ident): self
    {
        return new self(sprintf(
            'Cannot resolve the module manifest for module "%s".', $ident
        ));
    }
}
