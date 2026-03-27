<?php
declare(strict_types=1);

namespace Tests\Unit\Container\Fixtures;

class ClassWithMethods
{
    public function callableMethod(): bool
    {
        return false;
    }

    public static function callableStaticMethod(): bool
    {
        return true;
    }
}
