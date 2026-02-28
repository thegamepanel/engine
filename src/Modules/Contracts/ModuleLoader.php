<?php

namespace Engine\Modules\Contracts;

interface ModuleLoader
{
    /**
     * Load modules.
     *
     * @return array<string, array{0: \Engine\Modules\ModuleManifest, 1: \Engine\Modules\ModuleRegistrar|null}
     */
    public function load(): array;
}
