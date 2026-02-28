<?php
declare(strict_types=1);

namespace Engine\Modules\Composer;

use Composer\Package\BasePackage;
use Composer\Package\CompletePackageInterface;
use Composer\Script\Event;
use RuntimeException;

final readonly class ComposerModuleProcessor
{
    public const string PACKAGE_TYPE = 'tgp-module';

    public static function buildModules(Event $event): void
    {
        $composer = $event->getComposer();

        // Make sure the autoloader is required so we have access to it.
        self::requireAutoloader($composer->getConfig()->get('vendor-dir'));

        $packages = $composer->getRepositoryManager()
                             ->getLocalRepository()
                             ->getPackages();

        $modules = [];

        foreach ($packages as $package) {
            if ($package->getType() !== self::PACKAGE_TYPE) {
                continue;
            }

            // Get the path it's installed to.
            $path     = $composer->getInstallationManager()->getInstallPath($package);
            $manifest = ModuleManifestBuilder::fromPackage($package, $path);

            // Store the manifest.
            $modules[$manifest['ident']] = $manifest;
        }

        // Create the contents for a PHP file.
        $contents = '<?php' . PHP_EOL . PHP_EOL
                    . 'return ' . var_export($modules, true) . ';'
                    . PHP_EOL;

        // And write its contents to disk.
        file_put_contents(
            dirname($composer->getConfig()->get('vendor-dir')) . '/bootstrap/modules.php',
            $contents
        );
    }

    private static function buildModuleManifest(BasePackage $package, ?string $path): array
    {
        $extra = $package->getExtra()['tgp'];
        [$vendor, $ident] = explode('/', $package->getName(), 2);

        return [
            // Always available.
            'ident'        => $ident,
            'vendor'       => $vendor,
            'version'      => $package->getVersion(),
            'path'         => $path,

            // These are only available on CompletePackageInterface, which
            // shouldn't be an issue, but you never know.
            ...($package instanceof CompletePackageInterface ? [
                'description' => $package->getDescription(),
                'license'     => $package->getLicense(),
                'keywords'    => $package->getKeywords(),
                'authors'     => $package->getAuthors(),
                'support'     => $package->getSupport(),
            ] : []),

            // From extra.tgp
            'name'         => $extra['name'] ?? null,
            'registrar'    => $extra['registrar'] ?? null,
            'definition'   => $extra['definition'] ?? null,
            'icon'         => $extra['icon'] ?? null,
            'capabilities' => $extra['capabilities'] ?? [],
        ];
    }

    private static function requireAutoloader(string $vendorDir): void
    {
        $path = $vendorDir . '/autoload.php';

        if (! file_exists($path)) {
            throw new RuntimeException(sprintf('Composer autoloader not found at "%s".', $path));
        }

        require_once $path;
    }
}
