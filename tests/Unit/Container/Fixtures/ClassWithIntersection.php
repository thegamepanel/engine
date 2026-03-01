<?php
declare(strict_types=1);

namespace Tests\Unit\Container\Fixtures;

final class ClassWithIntersection
{
    public function __construct(
        public readonly SimpleInterface&SecondInterface $service
    )
    {
    }
}
