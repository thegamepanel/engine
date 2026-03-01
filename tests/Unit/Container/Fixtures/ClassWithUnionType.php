<?php
declare(strict_types=1);

namespace Tests\Unit\Container\Fixtures;

final class ClassWithUnionType
{
    public function __construct(
        public readonly SimpleInterface|SimpleClass $service
    )
    {
    }
}
