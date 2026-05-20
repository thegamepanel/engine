<?php
declare(strict_types=1);

namespace Tests\Unit\Config\Fixtures;

use Engine\Config\Contracts\ConfigObject;

final readonly class MultiKeyConfigObject implements ConfigObject
{
    public static function fromArray(array $data): static
    {
        return new self(
            value: $data['value'] ?? null,
            extra: $data['extra'] ?? null,
        );
    }

    public function __construct(
        public mixed $value = null,
        public mixed $extra = null,
    ) {
    }
}
