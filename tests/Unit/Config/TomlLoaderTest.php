<?php
declare(strict_types=1);

namespace Tests\Unit\Config;

use Engine\Config\Env;
use Engine\Config\Exceptions\InvalidConfigException;
use Engine\Config\Paths;
use Engine\Config\TomlLoader;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('unit'), Group('config'), Group('loader')]
class TomlLoaderTest extends TestCase
{
    private string $fixtureRoot;

    protected function setUp(): void
    {
        Env::destroy();

        $this->fixtureRoot = __DIR__ . '/Fixtures/toml';
        $previous          = $_ENV;
        $_ENV              = [];
        Env::createFromSuperglobal();
        $_ENV = $previous;
    }

    protected function tearDown(): void
    {
        Env::destroy();
    }

    // -------------------------------------------------------------------------
    // Basic main-file read
    // -------------------------------------------------------------------------

    /**
     * - load() parses config.toml and returns the tree.
     */
    #[Test]
    public function loadParsesMainConfigFile(): void
    {
        $tree = new TomlLoader()->load($this->pathsFor('basic'));

        $this->assertSame('Basic Config', $tree['title']);
        $this->assertSame(['primary' => 'default'], $tree['database']);
    }

    /**
     * - load() injects empty 'modules' and '__enabled_modules' keys when no module files exist.
     */
    #[Test]
    public function loadInjectsEmptyReservedKeysWhenNoModuleFiles(): void
    {
        $tree = new TomlLoader()->load($this->pathsFor('basic'));

        $this->assertSame([], $tree['modules']);
        $this->assertSame([], $tree['__enabled_modules']);
    }

    /**
     * - load() throws InvalidConfigException when the main config file is missing.
     */
    #[Test]
    public function loadThrowsWhenMainConfigMissing(): void
    {
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('Failed to read TOML file');
        $this->expectExceptionMessage('Unable to read file');

        new TomlLoader()->load($this->pathsFor('missing-main'));
    }

    // -------------------------------------------------------------------------
    // Drop-in merge
    // -------------------------------------------------------------------------

    /**
     * - Drop-in files override scalar keys from config.toml (last-wins).
     */
    #[Test]
    public function dropInsOverrideScalars(): void
    {
        $tree = new TomlLoader()->load($this->pathsFor('drop-ins'));

        $this->assertSame('Overridden', $tree['title']);
    }

    /**
     * - Drop-in files deep-merge nested tables with scalar override.
     */
    #[Test]
    public function dropInsDeepMergeNestedTables(): void
    {
        $tree = new TomlLoader()->load($this->pathsFor('drop-ins'));

        $this->assertSame('localhost', $tree['database']['connections']['default']['host']);
        $this->assertSame(5432, $tree['database']['connections']['default']['port']);
        $this->assertSame('from-02', $tree['database']['connections']['default']['password']);
    }

    /**
     * - Arrays are replaced wholesale by drop-ins, not concatenated.
     */
    #[Test]
    public function dropInsReplaceArraysWholesale(): void
    {
        $tree = new TomlLoader()->load($this->pathsFor('drop-ins'));

        $this->assertSame(['dropin-a', 'dropin-b'], $tree['database']['hosts']);
    }

    /**
     * - When only one side of a merge is an indexed list, the right side wins wholesale.
     *
     * Kills the `||` -> `&&` logical mutant in deepMerge.
     *
     * Base has database.hosts as an associative table; drop-in has database.hosts
     * as an indexed list. The drop-in (right side) must replace wholesale —
     * the base keys must NOT leak through.
     */
    #[Test]
    public function dropInWithOneSideIndexedReplacesWholesale(): void
    {
        $tree = new TomlLoader()->load($this->pathsFor('dropin-one-side-indexed'));

        $this->assertSame(['dropin-only'], $tree['database']['hosts']);
        $this->assertArrayNotHasKey('name', $tree['database']['hosts']);
        $this->assertArrayNotHasKey('fallback', $tree['database']['hosts']);
    }

    /**
     * - A drop-in with multiple top-level keys processes all of them, not just the first.
     *
     * Kills the `continue` -> `break` mutant in mergeDropIns. The drop-in's first
     * key triggers deep-merge with the base; the second key must still be picked up.
     */
    #[Test]
    public function dropInWithMultipleTopLevelKeysProcessesAll(): void
    {
        $tree = new TomlLoader()->load($this->pathsFor('dropin-multi-key'));

        $this->assertSame('dropin-host', $tree['database']['host']);
        $this->assertSame('must-survive', $tree['other']['flag']);
    }

    /**
     * - A drop-in scalar replaces a base table at the same key cleanly.
     *
     * Kills the LogicalAnd mutant in deepMerge that would turn the array+isset+array
     * triple-and check into ((array||isset) && array), which would invoke deepMerge
     * with a scalar right operand and explode.
     */
    #[Test]
    public function dropInScalarReplacesBaseTable(): void
    {
        $tree = new TomlLoader()->load($this->pathsFor('dropin-scalar-over-table'));

        $this->assertSame('disabled', $tree['database']['connections']['default']['options']);
    }

    /**
     * - Drop-ins and modules-enabled files are alphabetically sorted even when FS order differs.
     *
     * Kills the FunctionCallRemoval mutants on sort($files, SORT_STRING).
     */
    #[Test]
    public function dropInsAndModulesAreAlphabeticallySorted(): void
    {
        $tree = new TomlLoader()->load($this->pathsFor('sort-test'));

        // zzz-last.toml is applied AFTER aaa-first.toml because of alphabetical sort.
        // Both write to ordering.applied. Last-wins on scalars means "zzz" survives.
        $this->assertSame('zzz', $tree['ordering']['applied']);

        // modules-enabled list is alphabetical: aardvark before zeta.
        $this->assertSame(['aardvark', 'zeta'], $tree['__enabled_modules']);
    }

    /**
     * - Drop-ins apply in alphabetical filename order.
     *
     * (02-secrets.toml comes after 01-override.toml; its password value sticks.)
     */
    #[Test]
    public function dropInsApplyInAlphabeticalOrder(): void
    {
        $tree = new TomlLoader()->load($this->pathsFor('drop-ins'));

        $this->assertSame('from-02', $tree['database']['connections']['default']['password']);
    }

    /**
     * - Missing config.d/ directory loads cleanly with no drop-ins applied.
     */
    #[Test]
    public function missingDropInsDirLoadsCleanly(): void
    {
        $tree = new TomlLoader()->load($this->pathsFor('missing-dropins-dir'));

        $this->assertSame('No drop-in dir here', $tree['title']);
    }

    /**
     * - An empty drop-in table (parses to []) merges without affecting the base tree.
     *
     * Covers the empty-array branch in TomlLoader::isIndexed() which short-circuits
     * to "not a list" so the merge proceeds with a no-op foreach.
     */
    #[Test]
    public function emptyDropInLeavesBaseUnchanged(): void
    {
        $tree = new TomlLoader()->load($this->pathsFor('empty-dropin'));

        $this->assertSame('Base With Empty Drop-In', $tree['title']);
        $this->assertSame('localhost', $tree['database']['host']);
    }

    // -------------------------------------------------------------------------
    // Reserved-key assertion
    // -------------------------------------------------------------------------

    /**
     * - A top-level [modules] table in config.toml throws.
     */
    #[Test]
    public function reservedModulesKeyInMainThrows(): void
    {
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessageMatches('/"modules" is reserved/');

        new TomlLoader()->load($this->pathsFor('reserved-modules'));
    }

    /**
     * - A top-level __enabled_modules key in config.toml throws.
     */
    #[Test]
    public function reservedEnabledModulesKeyInMainThrows(): void
    {
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessageMatches('/"__enabled_modules" is reserved/');

        new TomlLoader()->load($this->pathsFor('reserved-enabled'));
    }

    /**
     * - A reserved key declared via a drop-in file throws, naming the drop-in path.
     */
    #[Test]
    public function reservedKeyInDropInThrows(): void
    {
        try {
            new TomlLoader()->load($this->pathsFor('reserved-via-dropin'));
            $this->fail('Expected InvalidConfigException.');
        } catch (InvalidConfigException $e) {
            $this->assertStringContainsString('"modules" is reserved', $e->getMessage());
            $this->assertStringContainsString('config.d/01-bad.toml', $e->getMessage());
        }
    }

    // -------------------------------------------------------------------------
    // modules-enabled/
    // -------------------------------------------------------------------------

    /**
     * - Files in modules-enabled/ are namespaced under modules.<filename>.* in the tree.
     */
    #[Test]
    public function modulesEnabledFilesAreNamespaced(): void
    {
        $tree = new TomlLoader()->load($this->pathsFor('modules'));

        $this->assertTrue($tree['modules']['admin']['debug']);
        $this->assertSame('v2', $tree['modules']['admin']['features']['panel']);
        $this->assertSame('stripe', $tree['modules']['billing']['provider']);
    }

    /**
     * - __enabled_modules is populated with the sorted list of module filenames (without .toml).
     */
    #[Test]
    public function enabledModulesListIsSortedAndExtensionStripped(): void
    {
        $tree = new TomlLoader()->load($this->pathsFor('modules'));

        $this->assertSame(['admin', 'billing'], $tree['__enabled_modules']);
    }

    /**
     * - A module filename containing a dot is rejected (dots would clash with the section separator).
     */
    #[Test]
    public function modulesEnabledFileWithDotInIdentifierThrows(): void
    {
        try {
            new TomlLoader()->load($this->pathsFor('dotted-module'));
            $this->fail('Expected InvalidConfigException.');
        } catch (InvalidConfigException $e) {
            $this->assertStringContainsString('admin.v2', $e->getMessage());
            $this->assertStringContainsString('contains a dot', $e->getMessage());
        }
    }

    /**
     * - Missing modules-enabled directory loads cleanly with empty list.
     */
    #[Test]
    public function missingModulesDirLoadsCleanly(): void
    {
        $tree = new TomlLoader()->load($this->pathsFor('missing-modules-dir'));

        $this->assertSame([], $tree['modules']);
        $this->assertSame([], $tree['__enabled_modules']);
    }

    // -------------------------------------------------------------------------
    // Env interpolation
    // -------------------------------------------------------------------------

    /**
     * - ${VAR} in a string scalar is replaced by the env value.
     */
    #[Test]
    public function envInterpolationReplacesScalar(): void
    {
        $this->withEnv([
            'APP_NAME'  => 'TGP',
            'DB_HOST'   => 'h',
            'DB_PORT'   => '3306',
            'DB_PASS'   => 'secret',
            'TAG_ONE'   => 't1',
            'TAG_TWO'   => 't2',
            'ADMIN_KEY' => 'k',
        ]);

        $tree = new TomlLoader()->load($this->pathsFor('env-interp'));

        $this->assertSame('TGP', $tree['title']);
        $this->assertSame('secret', $tree['database']['password']);
    }

    /**
     * - Multiple ${VAR} references in a single string all resolve.
     */
    #[Test]
    public function envInterpolationResolvesMultipleInOneString(): void
    {
        $this->withEnv([
            'APP_NAME'  => 'x',
            'DB_HOST'   => 'host',
            'DB_PORT'   => '5432',
            'DB_PASS'   => 'x',
            'TAG_ONE'   => 'x',
            'TAG_TWO'   => 'x',
            'ADMIN_KEY' => 'x',
        ]);

        $tree = new TomlLoader()->load($this->pathsFor('env-interp'));

        $this->assertSame('host:5432', $tree['dsn']);
    }

    /**
     * - ${VAR:-default} uses the default when the env var is not set.
     */
    #[Test]
    public function envInterpolationUsesDefaultWhenVarMissing(): void
    {
        $this->withEnv([
            'APP_NAME'  => 'x',
            'DB_HOST'   => 'h',
            'DB_PORT'   => '1',
            'DB_PASS'   => 'x',
            'TAG_ONE'   => 'x',
            'TAG_TWO'   => 'x',
            'ADMIN_KEY' => 'x',
        ]);

        $tree = new TomlLoader()->load($this->pathsFor('env-interp'));

        $this->assertSame('default-value', $tree['fallback']);
    }

    /**
     * - Interpolation walks into arrays and replaces string elements.
     */
    #[Test]
    public function envInterpolationReplacesInArrayElements(): void
    {
        $this->withEnv([
            'APP_NAME'  => 'x',
            'DB_HOST'   => 'h',
            'DB_PORT'   => '1',
            'DB_PASS'   => 'x',
            'TAG_ONE'   => 'first',
            'TAG_TWO'   => 'third',
            'ADMIN_KEY' => 'x',
        ]);

        $tree = new TomlLoader()->load($this->pathsFor('env-interp'));

        $this->assertSame(['first', 'literal-tag', 'third'], $tree['database']['tags']);
    }

    /**
     * - Non-string scalars (int, bool) pass through interpolation untouched.
     */
    #[Test]
    public function envInterpolationLeavesNonStringScalarsAlone(): void
    {
        $this->withEnv([
            'APP_NAME'  => 'x',
            'DB_HOST'   => 'h',
            'DB_PORT'   => '1',
            'DB_PASS'   => 'x',
            'TAG_ONE'   => 'x',
            'TAG_TWO'   => 'x',
            'ADMIN_KEY' => 'x',
        ]);

        $tree = new TomlLoader()->load($this->pathsFor('env-interp'));

        $this->assertSame(3306, $tree['database']['keep_int']);
        $this->assertTrue($tree['database']['keep_bool']);
    }

    /**
     * - Strings with no ${...} pattern pass through unchanged.
     */
    #[Test]
    public function envInterpolationLeavesLiteralStringsAlone(): void
    {
        $this->withEnv([
            'APP_NAME'  => 'x',
            'DB_HOST'   => 'h',
            'DB_PORT'   => '1',
            'DB_PASS'   => 'x',
            'TAG_ONE'   => 'x',
            'TAG_TWO'   => 'x',
            'ADMIN_KEY' => 'x',
        ]);

        $tree = new TomlLoader()->load($this->pathsFor('env-interp'));

        $this->assertSame('no-interp-here', $tree['literal']);
    }

    /**
     * - Interpolation also walks the modules namespace (module-file content).
     */
    #[Test]
    public function envInterpolationAppliesToModuleFiles(): void
    {
        $this->withEnv([
            'APP_NAME'  => 'x',
            'DB_HOST'   => 'h',
            'DB_PORT'   => '1',
            'DB_PASS'   => 'x',
            'TAG_ONE'   => 'x',
            'TAG_TWO'   => 'x',
            'ADMIN_KEY' => 'top-secret',
        ]);

        $tree = new TomlLoader()->load($this->pathsFor('env-interp'));

        $this->assertSame('top-secret', $tree['modules']['admin']['api_key']);
    }

    /**
     * - Missing env var with no default throws with the dotted path.
     */
    #[Test]
    public function envInterpolationMissingVarThrowsWithPath(): void
    {
        $this->withEnv([]);

        try {
            new TomlLoader()->load($this->pathsFor('env-missing'));
            $this->fail('Expected InvalidConfigException.');
        } catch (InvalidConfigException $e) {
            $this->assertStringContainsString('SECRET_THAT_IS_NOT_SET', $e->getMessage());
            $this->assertStringContainsString('database.password', $e->getMessage());
        }
    }

    /**
     * - Missing env var inside an array element reports the bracket-notation path.
     *
     * Kills the Concat / ConcatOperandRemoval mutants in interpolateEnv's bracket-path construction.
     */
    #[Test]
    public function envInterpolationMissingVarInsideArrayReportsBracketPath(): void
    {
        $this->withEnv([]);

        try {
            new TomlLoader()->load($this->pathsFor('env-missing-list'));
            $this->fail('Expected InvalidConfigException.');
        } catch (InvalidConfigException $e) {
            $this->assertStringContainsString('ALSO_NOT_SET', $e->getMessage());
            $this->assertStringContainsString('database.connections[0].password', $e->getMessage());
        }
    }

    // -------------------------------------------------------------------------
    // Parse error wrapping
    // -------------------------------------------------------------------------

    /**
     * - A malformed TOML file produces an InvalidConfigException wrapping the parser error.
     */
    #[Test]
    public function malformedTomlIsWrapped(): void
    {
        try {
            new TomlLoader()->load($this->pathsFor('malformed'));
            $this->fail('Expected InvalidConfigException.');
        } catch (InvalidConfigException $e) {
            $this->assertStringContainsString('config.toml', $e->getMessage());
            $this->assertNotNull($e->getPrevious());
        }
    }

    private function pathsFor(string $scenario): Paths
    {
        return new Paths(
            $this->fixtureRoot . '/' . $scenario,
            $this->fixtureRoot . '/' . $scenario . '/data',
            $this->fixtureRoot . '/' . $scenario . '/modules',
            $this->fixtureRoot . '/' . $scenario . '/cache',
            $this->fixtureRoot . '/' . $scenario . '/logs',
        );
    }

    /**
     * @param array<string, string> $env
     */
    private function withEnv(array $env): void
    {
        Env::destroy();
        $previous = $_ENV;
        $_ENV     = $env;
        Env::createFromSuperglobal();
        $_ENV = $previous;
    }
}
