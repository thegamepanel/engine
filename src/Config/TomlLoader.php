<?php
declare(strict_types=1);

namespace Engine\Config;

use Engine\Config\Exceptions\InvalidConfigException;
use Internal\Toml\Toml;
use Throwable;

/**
 * TOML Loader
 * -----------
 *
 * Reads the TOML config files described by a ConfigPaths instance and produces
 * a single merged array tree. The loader has no knowledge of config objects,
 * registries, or catalogues — it is pure file-to-array translation plus env
 * interpolation.
 *
 * Output tree shape:
 *   [
 *     ...user keys from config.toml + config.d/* merged...,
 *     'modules' => [filename => parsed-content, ...],
 *     '__enabled_modules' => [filename, filename, ...],
 *   ]
 */
final class TomlLoader
{
    /**
     * Load the config tree from the filesystem.
     *
     * @param Paths $paths
     *
     * @return array<string, mixed>
     *
     * @throws InvalidConfigException
     */
    public function load(Paths $paths): array
    {
        $configFile = $paths->config('config.toml');
        $tree       = $this->readMain($configFile);

        $this->assertNoReservedKeys($tree, $configFile);

        foreach ($this->readDropIns($paths->config('config.d')) as $file => $dropIn) {
            $this->assertNoReservedKeys($dropIn, $file);
            /** @var array<string, mixed> $tree */
            $tree = $this->deepMerge($tree, $dropIn);
        }

        [$moduleTrees, $enabledList] = $this->readModulesEnabled($paths->config('modules-enabled'));

        $tree['modules']           = $moduleTrees;
        $tree['__enabled_modules'] = $enabledList;

        /** @var array<string, mixed> $tree */
        $tree = $this->interpolateEnv($tree, '');

        return $tree;
    }

    /**
     * Walk the tree and substitute ${VAR} / ${VAR:-default} patterns in string scalars.
     *
     * @param array<int|string, mixed> $tree
     * @param string                   $path dotted path to current node, for error context
     *
     * @return array<int|string, mixed>
     *
     * @throws InvalidConfigException
     */
    private function interpolateEnv(array $tree, string $path): array
    {
        foreach ($tree as $key => $value) {
            if (is_int($key)) {
                $childPath = $path . '[' . $key . ']';
            } else {
                $childPath = $path === '' ? $key : $path . '.' . $key;
            }

            if (is_array($value)) {
                $tree[$key] = $this->interpolateEnv($value, $childPath);
            } else if (is_string($value)) {
                $tree[$key] = $this->interpolateString($value, $childPath);
            }
        }

        return $tree;
    }

    /**
     * Replace all ${VAR} / ${VAR:-default} substitutions in a single string.
     *
     * @param string $value
     * @param string $path
     *
     * @return string
     *
     * @throws InvalidConfigException
     */
    private function interpolateString(string $value, string $path): string
    {
        $pattern = '/\${([A-Z_][A-Z0-9_]*)(?::-(.*?))?}/';

        $result = preg_replace_callback(
            $pattern,
            function (array $match) use ($path): string {
                $variable = $match[1];
                $default  = $match[2] ?? null;

                if (Env::has($variable)) {
                    $envValue = Env::get($variable);

                    if ($envValue !== null) {
                        return (string) $envValue;
                    }
                }

                if ($default !== null) {
                    return $default;
                }

                throw InvalidConfigException::envVarMissing($variable, $path);
            },
            $value,
        );

        return $result ?? $value;
    }

    /**
     * Read modules-enabled/*.toml files, namespacing each under its filename.
     *
     * Returns a tuple [$moduleTrees, $enabledList] where $moduleTrees is keyed
     * by module identifier (filename minus .toml extension), and $enabledList
     * is a flat alphabetically-sorted list of those identifiers.
     *
     * @param string $directory
     *
     * @return array{0: array<string, array<string, mixed>>, 1: list<string>}
     *
     * @throws InvalidConfigException
     */
    private function readModulesEnabled(string $directory): array
    {
        if (! is_dir($directory)) {
            return [[], []];
        }

        $files = glob($directory . '/*.toml') ?: [];
        sort($files, SORT_STRING);

        $moduleTrees = [];
        $enabledList = [];

        foreach ($files as $file) {
            $name = basename($file, '.toml');

            if (str_contains($name, '.')) {
                throw InvalidConfigException::fileReadError(
                    $file,
                    sprintf('Module identifier "%s" contains a dot; module filenames must not contain dots (the loader uses them as section separators).', $name),
                );
            }

            $moduleTrees[$name] = $this->parse($file);
            $enabledList[]      = $name;
        }

        return [$moduleTrees, $enabledList];
    }

    /**
     * Reject user-authored top-level keys that the loader reserves for itself.
     *
     * Runs against the merged user tree before the loader injects its own
     * 'modules' and '__enabled_modules' keys, so it only flags user-authored
     * offenders.
     *
     * @param array<string, mixed> $tree
     * @param string               $sourceFile
     *
     * @throws InvalidConfigException
     */
    private function assertNoReservedKeys(array $tree, string $sourceFile): void
    {
        foreach (['modules', '__enabled_modules'] as $reserved) {
            if (array_key_exists($reserved, $tree)) {
                throw InvalidConfigException::reservedKey($reserved, $sourceFile);
            }
        }
    }

    /**
     * Parse the main config file.
     *
     * @param string $file
     *
     * @return array<string, mixed>
     *
     * @throws InvalidConfigException
     */
    private function readMain(string $file): array
    {
        return $this->parse($file);
    }

    /**
     * Scan and parse the drop-in directory, sorted alphabetically by filename.
     *
     * @param string $directory
     *
     * @return array<string, array<string, mixed>> filename => parsed tree
     *
     * @throws InvalidConfigException
     */
    private function readDropIns(string $directory): array
    {
        if (! is_dir($directory)) {
            return [];
        }

        $files = glob($directory . '/*.toml') ?: [];
        sort($files, SORT_STRING);

        $parsed = [];

        foreach ($files as $file) {
            $parsed[$file] = $this->parse($file);
        }

        return $parsed;
    }

    /**
     * Deep-merge a drop-in into the base tree.
     *
     * Scalars: last-wins. Nested tables: merge recursively. Indexed arrays
     * (including arrays-of-tables): wholesale replacement.
     *
     * @param array<int|string, mixed> $left
     * @param array<int|string, mixed> $right
     *
     * @return array<int|string, mixed>
     */
    private function deepMerge(array $left, array $right): array
    {
        if ($this->isIndexed($left) || $this->isIndexed($right)) {
            return $right;
        }

        foreach ($right as $key => $value) {
            if (is_array($value) && isset($left[$key]) && is_array($left[$key])) {
                $left[$key] = $this->deepMerge($left[$key], $value);
                continue;
            }

            $left[$key] = $value;
        }

        return $left;
    }

    /**
     * Whether the array is shaped like an indexed list (vs an associative table).
     *
     * Unlike PHP's array_is_list(), this returns false for an empty array — an
     * empty drop-in file parses to [] and we want it to merge as a no-op rather
     * than wholesale-replace the base tree. The wholesale-replacement semantic
     * (the `if ($this->isIndexed($left) || $this->isIndexed($right))` short
     * circuit in deepMerge) is reserved for non-empty lists where the intent is
     * unambiguous.
     *
     * @param array<int|string, mixed> $array
     *
     * @return bool
     */
    private function isIndexed(array $array): bool
    {
        if ($array === []) {
            return false;
        }

        return array_is_list($array);
    }

    /**
     * Parse a single TOML file into an array.
     *
     * @param string $file
     *
     * @return array<string, mixed>
     *
     * @throws InvalidConfigException
     */
    private function parse(string $file): array
    {
        // E_WARNING suppression is intentional: we handle the false return
        // ourselves, which is the source of truth for "file is missing or
        // unreadable" (readMain delegates here; drop-in and modules-enabled
        // paths come from glob() so the files are known to exist).
        $contents = @file_get_contents($file);

        if ($contents === false) {
            throw InvalidConfigException::fileReadError($file, 'Unable to read file.');
        }

        try {
            /** @var array<string, mixed> $parsed */
            $parsed = Toml::parseToArray($contents);

            return $parsed;
        } catch (Throwable $e) {
            throw InvalidConfigException::tomlParseError($file, $e);
        }
    }
}
