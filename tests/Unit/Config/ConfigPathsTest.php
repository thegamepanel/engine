<?php
declare(strict_types=1);

namespace Tests\Unit\Config;

use Engine\Config\Paths;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('unit'), Group('config'), Group('paths')]
class ConfigPathsTest extends TestCase
{
    /**
     * - The five paths are stored and read back unchanged.
     */
    #[Test]
    public function pathsRoundTrip(): void
    {
        $paths = new Paths(
            '/etc/tgp',
            '/var/lib/tgp',
            '/usr/lib/tgp/modules',
            '/var/cache/tgp',
            '/var/log/tgp',
        );

        $this->assertSame('/etc/tgp', $paths->config);
        $this->assertSame('/var/lib/tgp', $paths->data);
        $this->assertSame('/usr/lib/tgp/modules', $paths->modules);
        $this->assertSame('/var/cache/tgp', $paths->cache);
        $this->assertSame('/var/log/tgp', $paths->logs);
    }

    /**
     * - The five helper methods produce the correct absolute paths relative to the base path.
     */
    #[Test]
    public function pathsHelperMethods(): void
    {
        $paths = new Paths(
            '/etc/tgp',
            '/var/lib/tgp',
            '/usr/lib/tgp/modules',
            '/var/cache/tgp',
            '/var/log/tgp',
        );

        $this->assertSame('/etc/tgp/config.toml', $paths->config('config.toml'));
        $this->assertSame('/etc/tgp/config.d', $paths->config('config.d'));
        $this->assertSame('/var/lib/tgp/datafile.dat', $paths->data('datafile.dat'));
        $this->assertSame('/usr/lib/tgp/modules/mod1', $paths->modules('mod1'));
        $this->assertSame('/var/cache/tgp/cachefile.cache', $paths->cache('cachefile.cache'));
        $this->assertSame('/var/log/tgp/logfile.log', $paths->logs('logfile.log'));
    }

    /**
     * - The five helper methods strip unnecessary directory separators from the base path and the relative path.
     */
    #[Test]
    public function pathsHelperMethodsStripUnnecessaryDirectorySeparators(): void
    {
        $paths = new Paths(
            '/etc/tgp/',
            '/var/lib/tgp/',
            '/usr/lib/tgp/modules/',
            '/var/cache/tgp/',
            '/var/log/tgp/',
        );

        $this->assertSame('/etc/tgp/config.toml', $paths->config('/config.toml'));
        $this->assertSame('/etc/tgp/config.d', $paths->config('/config.d'));
        $this->assertSame('/var/lib/tgp/datafile.dat', $paths->data('/datafile.dat'));
        $this->assertSame('/usr/lib/tgp/modules/mod1', $paths->modules('/mod1'));
        $this->assertSame('/var/cache/tgp/cachefile.cache', $paths->cache('/cachefile.cache'));
        $this->assertSame('/var/log/tgp/logfile.log', $paths->logs('/logfile.log'));
    }
}
