<?php
declare(strict_types=1);

namespace Tests\Unit\Container\Fixtures;

/**
 * A fixture class with a private method, used to verify that the container refuses
 * to invoke non-public methods and instead throws an InvalidInvocationException.
 */
class ClassWithPrivateMethod
{
    private function secretMethod(): bool
    {
        return true;
    }
}
