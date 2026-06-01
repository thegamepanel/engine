<?php
declare(strict_types=1);

namespace Engine\Config;

/**
 * Config Paths
 * ------------
 *
 * Holds the three filesystem paths the config loader needs. This is a pure
 * value object; bootstrap is responsible for producing absolute paths.
 *
 * @phpstan-pure
 *
 * @immutable
 */
final readonly class ConfigPaths
{
    /**
     * @param string $configFile        Absolute path to the main config.toml file.
     * @param string $configDir         Absolute path to the config.d/ directory.
     * @param string $modulesEnabledDir absolute path to the modules-enabled/ directory
     */
    public function __construct(
        public string $configFile,
        public string $configDir,
        public string $modulesEnabledDir,
    ) {
    }
}
