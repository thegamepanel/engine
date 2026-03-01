<?php
declare(strict_types=1);

namespace Tests\Unit\Container\Fixtures;

final class HasPublicMethod
{
    public function greet(string $name): string
    {
        return 'Hello, ' . $name;
    }
}
