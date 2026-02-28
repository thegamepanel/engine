<?php
declare(strict_types=1);

namespace Engine\Modules\Composer;

use Composer\Package\BasePackage;
use Composer\Package\CompletePackageInterface;

final class ModuleManifestBuilder
{
    public static function fromPackage(BasePackage $package, ?string $path): array
    {
        $extra = $package->getExtra()['tgp'];
        [$vendor, $ident] = explode('/', $package->getName(), 2);

        return [
            // Always available.
            'ident'        => $ident,
            'vendor'       => $vendor,
            'version'      => $package->getVersion(),

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
            'definition'   => $extra['definition'] ?? null,
            'icon'         => $extra['icon'] ?? null,
            'capabilities' => $extra['capabilities'] ?? [],
        ];
    }
}
