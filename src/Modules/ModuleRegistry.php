<?php
declare(strict_types=1);

namespace Engine\Modules;

final readonly class ModuleRegistry
{
    /**
     * @var array<string, \Engine\Modules\ModuleManifest>
     */
    public readonly array $modules;

    /**
     * @var array<string>
     */
    public readonly array $coreModules;

    /**
     * @var array<string, \Engine\Modules\ModuleRegistrar<*>>
     */
    private readonly array $registrars;

    /**
     * @param array<string, \Engine\Modules\ModuleManifest>     $modules
     * @param array<string, \Engine\Modules\ModuleRegistrar<*>> $registrars
     */
    public function __construct(
        array $modules,
        array $registrars,
    )
    {
        $this->modules     = $modules;
        $this->coreModules = array_keys(
            array_filter($modules, static fn (ModuleManifest $module) => $module->isCoreModule())
        );
        $this->registrars  = $registrars;
    }

    /**
     * Get a modules' manifest.
     *
     * @param string $name
     *
     * @return \Engine\Modules\ModuleManifest|null
     */
    public function manifest(string $name): ?ModuleManifest
    {
        return $this->modules[$name] ?? null;
    }

    /**
     * Get a modules' registrar.
     *
     * @param string $name
     *
     * @return \Engine\Modules\ModuleRegistrar<*>|null
     */
    public function registrar(string $name): ?ModuleRegistrar
    {
        return $this->registrars[$name] ?? null;
    }
}
