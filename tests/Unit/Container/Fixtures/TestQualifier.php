<?php
declare(strict_types=1);

namespace Tests\Unit\Container\Fixtures;

use Engine\Container\Contracts\Qualifier;

class TestQualifier implements Qualifier
{
    public function equals(Qualifier $other): bool
    {
        return $other instanceof self;
    }
}
