<?php

namespace Engine\Config\Contracts;

/**
 * Config Object
 * -------------
 *
 * Interface represents the concept of a configuration object, used by the
 * config component. The interface is required because it ensures that the
 * <code>__set_state</code> magic method exists so that the object can be
 * cached.
 */
interface ConfigObject
{
    /**
     * Set the object state.
     *
     * This method is called by PHP when restoring an object exported via
     * <code>var_export()</code>, allowing cached config objects to be
     * reconstituted from their exported state.
     *
     * @param array<string|int, mixed> $data
     *
     * @return static
     */
    public static function __set_state(array $data): static;
}
