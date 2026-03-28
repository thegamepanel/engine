<?php
declare(strict_types=1);

namespace Engine\Config;

use Engine\Config\Contracts\ConfigObject;

/**
 *
 */
final readonly class ConfigRegistry
{
    /**
     * Create a map of class names to their module and config name.
     *
     * @param array<string, array<string, \Engine\Config\Contracts\ConfigObject>> $config
     *
     * @return array<class-string<\Engine\Config\Contracts\ConfigObject>, array{module: string, config: string}>
     */
    private static function mapConfigToClassMappings(array $config): array
    {
        $classMappings = [];

        foreach ($config as $module => $moduleConfig) {
            foreach ($moduleConfig as $configName => $configObject) {
                $classMappings[$configObject::class] = [
                    'module' => $module,
                    'config' => $configName,
                ];
            }
        }

        return $classMappings;
    }

    /**
     * The loaded config.
     *
     * @var array<string, array<string, \Engine\Config\Contracts\ConfigObject>>
     */
    private array $config;

    /**
     * A map of config object class names to their config details.
     *
     * @var array<class-string<\Engine\Config\Contracts\ConfigObject>, array{module: string, config: string}>
     */
    public array $classMappings;

    /**
     * @param array<string, array<string, \Engine\Config\Contracts\ConfigObject>> $config
     */
    public function __construct(
        array $config,
    )
    {
        $this->config        = $config;
        $this->classMappings = self::mapConfigToClassMappings($config);
    }

    /**
     * Check if a config entry exists by module and name.
     *
     * @param string $module
     * @param string $config
     *
     * @return bool
     */
    public function has(string $module, string $config): bool
    {
        // Config should always be a config object, so it's safe to assume
        // that if this is null, it doesn't exist.
        return $this->get($module, $config) !== null;
    }

    /**
     * Get a config object by module and name.
     *
     * @param string $module
     * @param string $config
     *
     * @return \Engine\Config\Contracts\ConfigObject|null
     */
    public function get(string $module, string $config): ?ConfigObject
    {
        return $this->config[$module][$config] ?? null;
    }

    /**
     * Get a config object by its class.
     *
     * @param class-string<\Engine\Config\Contracts\ConfigObject> $class
     *
     * @return \Engine\Config\Contracts\ConfigObject|null
     */
    public function for(string $class): ?ConfigObject
    {
        $mapping = $this->classMappings[$class] ?? null;

        if ($mapping === null) {
            return null;
        }

        return $this->get($mapping['module'], $mapping['config']);
    }
}
