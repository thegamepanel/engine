<?php

namespace Engine\Collectors\Contracts;

use Engine\Modules\ModuleManifest;
use Engine\OperatingContext;

/**
 * @template TCollector of \Engine\Collectors\Contracts\Collector
 */
interface CollectorHandler
{
    /**
     * The class of the collector this handler uses.
     *
     * @return class-string<\Engine\Collectors\Contracts\Collector>
     */
    public function collects(): string;

    /**
     * Create a collector instance.
     *
     * @param \Engine\Modules\ModuleManifest $manifest
     * @param \Engine\OperatingContext|null  $operatingContext
     * @param bool                           $scoped
     *
     * @return TCollector
     */
    public function create(ModuleManifest $manifest, ?OperatingContext $operatingContext = null, bool $scoped = true): Collector;
}
