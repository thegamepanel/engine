<?php

namespace Engine\Collectors\Contracts;

use Engine\Core\OperatingContext;
use Engine\Modules\ModuleManifest;

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
    public static function collects(): string;

    /**
     * Create a collector instance.
     *
     * @param \Engine\Modules\ModuleManifest     $manifest
     * @param \Engine\Core\OperatingContext|null $operatingContext
     * @param bool                               $scoped
     *
     * @return Collector
     */
    public function create(ModuleManifest $manifest, ?OperatingContext $operatingContext = null, bool $scoped = true): Collector;

    /**
     * Process a collector instance for a module.
     *
     * @param Collector                          $collector
     * @param \Engine\Modules\ModuleManifest     $manifest
     * @param \Engine\Core\OperatingContext|null $operatingContext
     * @param bool                               $scoped
     *
     * @return Collector
     */
    public function process(Collector $collector, ModuleManifest $manifest, ?OperatingContext $operatingContext = null, bool $scoped = true): Collector;

    /**
     * Perform any required finalisation.
     *
     * @return void
     */
    public function finalise(): void;
}
