<?php
declare(strict_types=1);

namespace Tests\Unit\Container\Fixtures;

final class InvokableClass
{
    public function __invoke(string $value): string
    {
        return 'invoked:' . $value;
    }
}
