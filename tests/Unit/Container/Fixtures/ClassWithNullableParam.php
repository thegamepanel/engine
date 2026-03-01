<?php
declare(strict_types=1);

namespace Tests\Unit\Container\Fixtures;

final class ClassWithNullableParam
{
    public function __construct(
        public readonly ?SimpleInterface $service = null
    )
    {
    }
}
