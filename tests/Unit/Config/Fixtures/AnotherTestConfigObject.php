<?php
declare(strict_types=1);

namespace Tests\Unit\Config\Fixtures;

use Engine\Config\Contracts\ConfigObject;

final readonly class AnotherTestConfigObject implements ConfigObject
{
    public static function fromArray(array $data): static
    {
        return new self($data['value'] ?? null);
    }

    public function __construct(
        public mixed $value = null,
    ) {
    }
}
