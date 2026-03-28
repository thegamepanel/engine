<?php
declare(strict_types=1);

namespace Tests\Unit\Container\Fixtures;

/**
 * A fixture class whose constructor has one auto-wireable typed parameter followed
 * by a variadic parameter typed as an unresolvable interface. Used to test that the
 * container stops collecting dependencies when it encounters a variadic parameter,
 * rather than attempting to resolve it (which would fail for the interface type).
 */
class ClassWithVariadicParam
{
    public function __construct(
        public readonly ClassWithMethods $first,
        AbstractInterface ...$rest,
    ) {
    }
}
