<?php
declare(strict_types=1);

namespace Tests\Unit\Container\Fixtures;

final class HasPrivateMethod
{
    private function secret(): string
    {
        return 'hidden';
    }
}
