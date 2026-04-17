<?php
declare(strict_types=1);

namespace Engine\Config;

use Engine\Config\Contracts\ConfigObject;

/**
 * Base Config Object
 * ------------------
 *
 * Provides a default implementation of the <code>__set_state</code> magic for
 * implementations of {@see ConfigObject}.
 *
 * @template TState of array<string|int, mixed>
 *
 * @phpstan-pure
 *
 * @immutable
 */
abstract readonly class BaseConfigObject implements ConfigObject
{
    /**
     * Set the object state.
     *
     * This method is called when unserializing a persisted object from a
     * cached config.
     *
     * @param TState $data
     *
     * @return static<TState>
     *
     * @noinspection PhpUnnecessaryLocalVariableInspection
     */
    public static function __set_state(array $data): static
    {
        /** @phpstan-ignore new.static */
        $instance = new static(...$data);

        /** @var static<TState> */
        return $instance;
    }
}
