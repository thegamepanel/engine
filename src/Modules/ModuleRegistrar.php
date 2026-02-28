<?php
declare(strict_types=1);

namespace Engine\Modules;

use Engine\Collectors\Contracts\Collector;
use Engine\Core\EngineBuilder;
use Engine\Core\OperatingContext;

/**
 * @template TRegistrar of object
 */
final readonly class ModuleRegistrar
{
    /**
     * @param class-string<TRegistrar>                                                           $class
     * @param string|null                                                                        $registerMethod
     * @param string|null                                                                        $bootMethod
     * @param array<string, array<class-string<\Engine\Collectors\Contracts\Collector>, string>> $unscopedCollectors
     * @param array<string, array<class-string<\Engine\Collectors\Contracts\Collector>, string>> $scopedCollectors
     * @param array<class-string<\Engine\Collectors\Contracts\Collector>, array<string>>         $unscopedNoContextCollectors
     * @param array<class-string<\Engine\Collectors\Contracts\Collector>, array<string>>         $scopedNoContextCollectors
     *
     */
    public function __construct(
        public string  $class,
        public ?string $registerMethod,
        public ?string $bootMethod,
        public array   $unscopedCollectors,
        public array   $scopedCollectors,
        public array   $unscopedNoContextCollectors,
        public array   $scopedNoContextCollectors,
    )
    {
    }

    /**
     * Call the register method if there is one.
     *
     * @param TRegistrar                 $registrar
     * @param \Engine\Core\EngineBuilder $builder
     *
     * @return void
     */
    public function register(object $registrar, EngineBuilder $builder): void
    {
        if ($this->registerMethod) {
            $registrar->{$this->registerMethod}($builder);
        }
    }

    /**
     * Call the boot method if there is one.
     *
     * @param TRegistrar $registrar
     *
     * @return void
     */
    public function boot(object $registrar): void
    {
        if ($this->bootMethod) {
            $registrar->{$this->bootMethod}();
        }
    }

    /**
     * Run the collector collection method if there is one.
     *
     * @template TCollector of \Engine\Collectors\Contracts\Collector
     *
     * @param TRegistrar                         $registrar
     * @param \Engine\Core\OperatingContext|null $operatingContext
     * @param class-string<TCollector>           $collectorClass
     * @param TCollector                         $collector
     * @param bool                               $scoped
     *
     * @return void
     */
    public function collect(
        object            $registrar,
        ?OperatingContext $operatingContext,
        string            $collectorClass,
        Collector         $collector,
        bool              $scoped = true
    ): void
    {
        // Get the relevant collectors for the operating context and scope.
        if ($operatingContext === null) {
            if ($scoped) {
                $method = $this->scopedNoContextCollectors[$collectorClass] ?? null;
            } else {
                $method = $this->unscopedNoContextCollectors[$collectorClass] ?? null;
            }
        } else if ($scoped) {
            $method = $this->scopedCollectors[$operatingContext->value][$collectorClass] ?? null;
        } else {
            $method = $this->unscopedCollectors[$operatingContext->value][$collectorClass] ?? null;
        }

        if ($method !== null) {
            $registrar->{$method}($collector);
        }
    }
}
