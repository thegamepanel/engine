<?php
declare(strict_types=1);

namespace Tests\Unit\Container\Fixtures;

final class ClassWithScalarDefaults
{
    public function __construct(
        public readonly string $name = 'default',
        public readonly int    $count = 0,
    )
    {
    }
}
