<?php
declare(strict_types=1);

namespace Tests\Unit\Container\Fixtures;

use Engine\Container\Attributes\Ghost;

final class ClassWithGhostParam
{
    public function __construct(
        #[Ghost] public readonly SimpleClass $dependency
    )
    {
    }
}
