<?php
declare(strict_types=1);

namespace Tests\Unit\Container\Fixtures;

final class HasStaticMethod
{
    public static function greet(string $name): string
    {
        return 'Hello, ' . $name;
    }
}
