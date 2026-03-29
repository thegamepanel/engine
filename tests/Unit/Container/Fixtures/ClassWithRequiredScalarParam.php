<?php
declare(strict_types=1);

namespace Tests\Unit\Container\Fixtures;

/**
 * A fixture class with a required, non-nullable scalar constructor parameter and no
 * default value. Used to exercise the path in GenericResolver where a built-in type
 * cannot be resolved, has no default, and does not allow null — causing a
 * DependencyResolutionException to be thrown.
 */
class ClassWithRequiredScalarParam
{
    public static function staticValue(): string
    {
        return 'static-result';
    }

    public function __construct(public readonly string $value)
    {
    }
}
