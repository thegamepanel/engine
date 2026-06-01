<?php
declare(strict_types=1);

namespace Tests\Unit\Config\Fixtures;

use Engine\Config\Contracts\ConfigObject;
use RuntimeException;

final readonly class ThrowingConfigObject implements ConfigObject
{
    public static function fromArray(array $data): static
    {
        throw new RuntimeException('boom');
    }

    private function __construct()
    {
    }
}
