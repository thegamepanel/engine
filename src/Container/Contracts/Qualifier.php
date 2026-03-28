<?php

namespace Engine\Container\Contracts;

/**
 * Qualifier Contract
 * ------------------
 *
 * Marks a PHP attribute as a dependency injection qualifier.
 */
interface Qualifier
{
    /**
     * Check if the qualifier is equal to another.
     *
     * @param Qualifier $other
     *
     * @return bool
     */
    public function equals(self $other): bool;
}
