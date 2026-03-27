<?php
declare(strict_types=1);

namespace Tests\Unit\Container\Fixtures;

class ClassWithProperty
{
    public string $value;

    public function __construct()
    {
        $this->value = 'initialized';
    }
}
