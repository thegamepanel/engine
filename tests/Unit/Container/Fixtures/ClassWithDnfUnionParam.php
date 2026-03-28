<?php
declare(strict_types=1);

namespace Tests\Unit\Container\Fixtures;

use Countable;
use Stringable;

/**
 * A fixture class with a DNF (Disjunctive Normal Form) union constructor parameter
 * containing an intersection type and null ((Stringable&Countable)|null). Used to
 * test GenericResolver's handling of intersection subtypes within a union, specifically
 * the path where a ReflectionIntersectionType is extracted from the union and resolved.
 */
class ClassWithDnfUnionParam
{
    public function __construct(public readonly (Countable&Stringable)|null $dep)
    {
    }
}
