<?php
declare(strict_types=1);

namespace Tests\Unit\Container\Fixtures;

/**
 * A fixture class that implements __invoke so it acts as an invokable callable.
 * Used to exercise the invokable-object branch of ReflectionHelper::getFunctionName.
 */
class InvokableClass
{
    public function __invoke(): bool
    {
        return true;
    }
}
