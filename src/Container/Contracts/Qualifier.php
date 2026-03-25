<?php

namespace Engine\Container\Contracts;

/**
 * Qualifier Contract
 * ------------------
 *
 * Marks a PHP attribute as a dependency injection qualifier.
 *
 * @package Container
 */
interface Qualifier
{
    /**
     * Check if the qualifier is equal to another.
     *
     * @param \Engine\Container\Contracts\Qualifier $other
     *
     * @return bool
     */
    public function equals(self $other): bool;
}
