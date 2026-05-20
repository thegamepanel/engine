<?php
declare(strict_types=1);

namespace Engine\Config;

use Engine\Config\Contracts\ConfigObject;
use Engine\Config\Exceptions\ConfigLifecycleException;
use Engine\Config\Exceptions\ConfigNotRegisteredException;
use Engine\Config\Exceptions\InvalidConfigException;
use Throwable;

/**
 * Config Registry
 * ---------------
 *
 * Mutable bootstrap-phase counterpart to ConfigCatalogue. Has a three-phase
 * lifecycle: open (after construction), core-sealed (after sealCore()), and
 * fully sealed (after seal()). Core configs hydrate during sealCore() and are
 * readable via for() between the two seal calls. Module configs register
 * during the middle phase and hydrate during seal().
 */
final class ConfigRegistry
{
    private const string CORE_MODULE = 'engine';

    /**
     * @var array<class-string<ConfigObject>, ConfigObject>
     */
    private array $hydratedCore = [];

    /**
     * @var array<class-string<ConfigObject>, array{module: string, name: string, section: string}>
     */
    private array $moduleRegistrations = [];

    private bool $coreSealed = false;

    private bool $sealed = false;

    /**
     * @param array<string, mixed>                      $tree        raw merged TOML tree from TomlLoader
     * @param array<string, class-string<ConfigObject>> $coreMapping TOML top-level key => core config class
     */
    public function __construct(
        private readonly array $tree,
        private readonly array $coreMapping,
    ) {
    }

    /**
     * Hydrate every core mapping and freeze the core surface.
     *
     * @throws InvalidConfigException
     * @throws ConfigLifecycleException
     */
    public function sealCore(): void
    {
        if ($this->coreSealed) {
            throw ConfigLifecycleException::coreAlreadySealed();
        }

        foreach ($this->coreMapping as $tomlKey => $class) {
            $section = $this->tree[$tomlKey] ?? [];

            if (! is_array($section)) {
                throw InvalidConfigException::hydrationFailed(
                    file: 'config.toml',
                    section: $tomlKey,
                    message: 'Expected an array at the section path.',
                );
            }

            /** @var array<array-key, mixed> $section */
            $this->hydratedCore[$class] = $this->hydrate(
                class: $class,
                data: $section,
                file: 'config.toml',
                section: $tomlKey,
            );
        }

        $this->coreSealed = true;
    }

    /**
     * Read a hydrated config object between sealCore() and seal().
     *
     * @template TConfig of ConfigObject
     *
     * @param class-string<TConfig> $class
     *
     * @return TConfig
     *
     * @throws ConfigNotRegisteredException
     * @throws ConfigLifecycleException
     */
    public function for(string $class): ConfigObject
    {
        if (! $this->coreSealed) {
            throw ConfigLifecycleException::readBeforeCoreSeal();
        }

        if ($this->sealed) {
            throw ConfigLifecycleException::readAfterSeal();
        }

        if (! isset($this->hydratedCore[$class])) {
            throw ConfigNotRegisteredException::forClass($class);
        }

        /** @var TConfig */
        return $this->hydratedCore[$class];
    }

    /**
     * Register a module config class for hydration during seal().
     *
     * @param string                     $module
     * @param string                     $name
     * @param class-string<ConfigObject> $class
     *
     * @throws ConfigLifecycleException
     */
    public function register(string $module, string $name, string $class): void
    {
        if ($this->sealed) {
            throw ConfigLifecycleException::registerAfterSeal();
        }

        if (! $this->coreSealed) {
            throw ConfigLifecycleException::registerBeforeCoreSeal();
        }

        if ($module === self::CORE_MODULE) {
            throw ConfigLifecycleException::registerAsCore($module);
        }

        if (str_contains($module, '.') || str_contains($name, '.')) {
            throw ConfigLifecycleException::dottedModuleOrName($module, $name);
        }

        $this->moduleRegistrations[$class] = [
            'module'  => $module,
            'name'    => $name,
            'section' => 'modules.' . $module . '.' . $name,
        ];
    }

    /**
     * Hydrate every module registration and return the final immutable catalogue.
     *
     * @throws InvalidConfigException
     * @throws ConfigLifecycleException
     */
    public function seal(): ConfigCatalogue
    {
        if ($this->sealed) {
            throw ConfigLifecycleException::alreadySealed();
        }

        if (! $this->coreSealed) {
            throw ConfigLifecycleException::sealBeforeCoreSeal();
        }

        /** @var array<string, array<string, ConfigObject>> $nested */
        $nested = [];

        if ($this->hydratedCore !== []) {
            foreach ($this->coreMapping as $tomlKey => $class) {
                $nested[self::CORE_MODULE][$tomlKey] = $this->hydratedCore[$class];
            }
        }

        foreach ($this->moduleRegistrations as $class => $registration) {
            /** @var class-string<ConfigObject> $class */
            $moduleFile = 'modules-enabled/' . $registration['module'] . '.toml';
            $section    = $this->pluck($this->tree, $registration['section'], $moduleFile);

            $hydrated = $this->hydrate(
                class: $class,
                data: $section,
                file: $moduleFile,
                section: $registration['name'],
            );

            $nested[$registration['module']][$registration['name']] = $hydrated;
        }

        $this->sealed = true;

        return new ConfigCatalogue($nested);
    }

    /**
     * Hydrate a single section through its config class, wrapping any failure.
     *
     * @param class-string<ConfigObject> $class
     * @param array<array-key, mixed>    $data
     * @param string                     $file
     * @param string                     $section
     *
     * @return ConfigObject
     *
     * @throws InvalidConfigException
     */
    private function hydrate(string $class, array $data, string $file, string $section): ConfigObject
    {
        try {
            return $class::fromArray($data);
        } catch (Throwable $e) {
            throw InvalidConfigException::hydrationFailed(
                file: $file,
                section: $section,
                message: $e->getMessage(),
                previous: $e,
            );
        }
    }

    /**
     * Walk a dotted path into the tree. Missing intermediate -> [].
     * Non-array intermediate -> InvalidConfigException.
     *
     * @param array<array-key, mixed> $tree
     * @param string                  $dottedPath
     * @param string                  $file
     *
     * @return array<array-key, mixed>
     *
     * @throws InvalidConfigException
     */
    private function pluck(array $tree, string $dottedPath, string $file): array
    {
        $segments = explode('.', $dottedPath);
        $current  = $tree;

        foreach ($segments as $segment) {
            if (! is_array($current)) {
                throw InvalidConfigException::hydrationFailed(
                    file: $file,
                    section: $dottedPath,
                    message: 'Expected an array at intermediate path segment.',
                );
            }

            if (! array_key_exists($segment, $current)) {
                return [];
            }

            $current = $current[$segment];
        }

        if (! is_array($current)) {
            throw InvalidConfigException::hydrationFailed(
                file: $file,
                section: $dottedPath,
                message: 'Expected an array at the section path.',
            );
        }

        /** @var array<array-key, mixed> $current */
        return $current;
    }
}
