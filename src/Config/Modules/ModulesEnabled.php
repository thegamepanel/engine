<?php
declare(strict_types=1);

namespace Engine\Config\Modules;

use Engine\Config\Contracts\ConfigObject;
use Webmozart\Assert\Assert;

/**
 * Modules Enabled
 * ---------------
 *
 * Core config object holding the flat list of enabled module identifiers. The
 * list is populated by the loader from the filenames in the modules-enabled/
 * directory. It is hydrated during sealCore() so the module system can read it
 * before module config registration begins.
 *
 * @phpstan-pure
 *
 * @immutable
 */
final readonly class ModulesEnabled implements ConfigObject
{
    /**
     * Create a new ModulesEnabled object from an array.
     *
     * The input is expected to be a list of strings (each a module identifier).
     *
     * @param array<string, mixed> $data
     *
     * @return static
     */
    public static function fromArray(array $data): static
    {
        Assert::allString($data, 'Enabled modules must be a list of strings.');

        /** @var list<string> $modules */
        $modules = array_values($data);

        return new self($modules);
    }

    /**
     * @param list<string> $modules
     */
    private function __construct(
        public array $modules,
    ) {
    }

    /**
     * Check whether a module identifier is enabled.
     *
     * @param string $module
     *
     * @return bool
     */
    public function has(string $module): bool
    {
        return in_array($module, $this->modules, strict: true);
    }
}
