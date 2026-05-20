<?php
declare(strict_types=1);

namespace Tests\Unit\Config;

use Engine\Config\ConfigPaths;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('unit'), Group('config'), Group('paths')]
class ConfigPathsTest extends TestCase
{
    /**
     * - The three paths are stored and read back unchanged.
     */
    #[Test]
    public function pathsRoundTrip(): void
    {
        $paths = new ConfigPaths(
            configFile: '/etc/tgp/config.toml',
            configDir: '/etc/tgp/config.d',
            modulesEnabledDir: '/etc/tgp/modules-enabled',
        );

        $this->assertSame('/etc/tgp/config.toml', $paths->configFile);
        $this->assertSame('/etc/tgp/config.d', $paths->configDir);
        $this->assertSame('/etc/tgp/modules-enabled', $paths->modulesEnabledDir);
    }
}
