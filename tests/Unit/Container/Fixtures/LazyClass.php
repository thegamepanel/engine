<?php
declare(strict_types=1);

namespace Tests\Unit\Container\Fixtures;

use Engine\Container\Attributes\Lazy;

/**
 * A fixture class decorated with {@see Lazy} to support testing that the class-level
 * attribute triggers lazy proxy creation during resolution. The typed property is
 * required so PHP can produce a real uninitialized lazy proxy object.
 */
#[Lazy]
class LazyClass
{
    public string $value;

    public function __construct()
    {
        $this->value = 'initialized';
    }
}
