<?php
declare(strict_types=1);

namespace Tests\Unit\Config\Fixtures;

use Engine\Config\Contracts\ConfigObject;

final class TestConfigObject implements ConfigObject
{
    public static function __set_state(array $data): static
    {
        return new self();
    }
}
