<?php
declare(strict_types=1);

namespace Engine\Modules;

/**
 * Module Manifest
 *
 * Represents the manifest of an individual module.
 */
final readonly class ModuleManifest
{
    /**
     * @param bool                              $core
     * @param string                            $ident
     * @param string                            $name
     * @param string|null                       $description
     * @param array<\Engine\Modules\Capability> $capabilities
     * @param class-string                      $registrar
     */
    public function __construct(
        public bool    $core,
        public string  $ident,
        public string  $name,
        public ?string $description,
        public array   $capabilities,
        public string  $registrar
    )
    {
    }

    /**
     * Returns whether the module is a core module.
     *
     * Core modules cannot be installed, updated, or uninstalled. They are part
     * of the core engine.
     *
     * @return bool
     */
    public function isCoreModule(): bool
    {
        return $this->core;
    }
}
