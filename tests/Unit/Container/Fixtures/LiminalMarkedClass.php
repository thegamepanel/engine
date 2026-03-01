<?php
declare(strict_types=1);

namespace Tests\Unit\Container\Fixtures;

use Engine\Container\Attributes\Liminal;

#[Liminal]
final class LiminalMarkedClass
{
    public function __construct(
        public readonly string $value = 'liminal'
    )
    {
    }
}
