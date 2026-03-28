<?php
declare(strict_types=1);

namespace Tests\Unit\Container\Fixtures;

/**
 * A fixture class with a nullable scalar constructor parameter and no default value.
 * Used to exercise the path in GenericResolver where a built-in type allows null,
 * causing the resolver to return null rather than throwing.
 */
class ClassWithNullableScalarParam
{
    public function __construct(public readonly ?string $value)
    {
    }
}
