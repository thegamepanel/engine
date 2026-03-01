<?php
declare(strict_types=1);

namespace Tests\Unit\Container\Fixtures;

final class ClassWithDependency
{
    public function __construct(
        public readonly SimpleClass $dependency
    )
    {
    }
}
