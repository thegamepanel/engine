<?php
declare(strict_types=1);

namespace Engine\Config;

/**
 * Paths
 * ------------
 *
 * Holds the all filesystem paths needed by the eninge. This is a pure
 * value object; bootstrap is responsible for producing absolute paths.
 *
 * @phpstan-pure
 *
 * @immutable
 */
final readonly class Paths
{
    /**
     * @param string $config  Absolute path to the root of the config directory, the directory containing config.toml and config.d/
     * @param string $data    Absolute path to the data directory
     * @param string $modules Absolute path to whether the modules are stored
     * @param string $cache   Absolute path to the cache directory
     * @param string $logs    Absolute path to the logs directory
     */
    public function __construct(
        public string $config,
        public string $data,
        public string $modules,
        public string $cache,
        public string $logs,
    ) {
    }

    /**
     * Helpers to get a path relative to the config directory.
     *
     * @param string $path
     *
     * @return string
     */
    public function config(string $path): string
    {
        return rtrim($this->config, DIRECTORY_SEPARATOR)
               . DIRECTORY_SEPARATOR
               . ltrim($path, DIRECTORY_SEPARATOR);
    }

    /**
     * Helpers to get a path relative to the data directory.
     *
     * @param string $path
     *
     * @return string
     */
    public function data(string $path): string
    {
        return rtrim($this->data, DIRECTORY_SEPARATOR)
               . DIRECTORY_SEPARATOR
               . ltrim($path, DIRECTORY_SEPARATOR);
    }

    /**
     * Helpers to get a path relative to the modules directory.
     *
     * @param string $path
     *
     * @return string
     */
    public function modules(string $path): string
    {
        return rtrim($this->modules, DIRECTORY_SEPARATOR)
               . DIRECTORY_SEPARATOR
               . ltrim($path, DIRECTORY_SEPARATOR);
    }

    /**
     * Helpers to get a path relative to the cache directory.
     *
     * @param string $path
     *
     * @return string
     */
    public function cache(string $path): string
    {
        return rtrim($this->cache, DIRECTORY_SEPARATOR)
               . DIRECTORY_SEPARATOR
               . ltrim($path, DIRECTORY_SEPARATOR);
    }

    /**
     * Helpers to get a path relative to the logs directory.
     *
     * @param string $path
     *
     * @return string
     */
    public function logs(string $path): string
    {
        return rtrim($this->logs, DIRECTORY_SEPARATOR)
               . DIRECTORY_SEPARATOR
               . ltrim($path, DIRECTORY_SEPARATOR);
    }
}
