<?php
declare(strict_types=1);

namespace Engine\Config;

use Engine\Config\Contracts\ConfigObject;
use Engine\Config\Modules\ModulesEnabled;

/**
 * Core Config Mapping
 * -------------------
 *
 * Canonical map of TOML top-level keys to core config classes. Core configs are
 * those that must be hydrated and available before the module system runs.
 * Anything that ships with the engine but is consumed after module discovery
 * (e.g. DatabaseConfig) is NOT a core config — it goes through ConfigRegistry::register().
 *
 * Currently the only true core config is ModulesEnabled, since the module
 * system depends on it. Other core configs will be added here as they emerge.
 */
final class CoreConfig
{
    /**
     * @var array<string, class-string<ConfigObject>>
     */
    public const array MAPPING = [
        '__enabled_modules' => ModulesEnabled::class,
    ];

    /**
     * @codeCoverageIgnore
     */
    private function __construct()
    {
    }
}
