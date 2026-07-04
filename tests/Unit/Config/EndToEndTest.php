<?php
declare(strict_types=1);

namespace Tests\Unit\Config;

use Engine\Config\ConfigRegistry;
use Engine\Config\CoreConfig;
use Engine\Config\Env;
use Engine\Config\Modules\ModulesEnabled;
use Engine\Config\Paths;
use Engine\Config\TomlLoader;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Unit\Config\Fixtures\TestConfigObject;

#[Group('unit'), Group('config'), Group('e2e')]
class EndToEndTest extends TestCase
{
    protected function setUp(): void
    {
        Env::destroy();

        $previous = $_ENV;
        $_ENV     = [
            'APP_NAME'    => 'EngineTest',
            'ADMIN_VALUE' => 'hello',
        ];
        Env::createFromSuperglobal();
        $_ENV = $previous;
    }

    protected function tearDown(): void
    {
        Env::destroy();
    }

    /**
     * - A real fixture flows through loader -> registry -> catalogue end to end.
     */
    #[Test]
    public function happyPath(): void
    {
        $paths = new Paths(
            __DIR__ . '/Fixtures/toml/e2e/',
            __DIR__ . '/Fixtures/toml/e2e/data/',
            __DIR__ . '/Fixtures/toml/e2e/modules/',
            __DIR__ . '/Fixtures/toml/e2e/cache/',
            __DIR__ . '/Fixtures/toml/e2e/logs/',
        );

        $tree     = new TomlLoader()->load($paths);
        $registry = new ConfigRegistry($tree, CoreConfig::MAPPING);

        $registry->sealCore();

        $enabled = $registry->for(ModulesEnabled::class);
        $this->assertSame(['admin'], $enabled->modules);

        $registry->register('admin', 'main', TestConfigObject::class);

        $catalogue = $registry->seal();

        $admin = $catalogue->for(TestConfigObject::class);
        $this->assertSame('hello', $admin->value);

        $core = $catalogue->for(ModulesEnabled::class);
        $this->assertSame(['admin'], $core->modules);

        // The base + drop-in + env interpolation all landed in the tree.
        $this->assertSame('EngineTest', $tree['title']);
        $this->assertSame('from-dropin', $tree['secret']);
    }
}
