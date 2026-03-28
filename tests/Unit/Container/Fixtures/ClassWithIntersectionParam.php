<?php
declare(strict_types=1);

namespace Tests\Unit\Container\Fixtures;

use Countable;
use Stringable;

/**
 * A fixture class with an intersection-typed constructor parameter (Stringable&Countable).
 * Used to drive tests for GenericResolver's intersection type resolution paths, including
 * the no-binding, satisfying-instance, and unsatisfying-instance cases.
 */
class ClassWithIntersectionParam
{
    public function __construct(public readonly Stringable&Countable $dep)
    {
    }
}
