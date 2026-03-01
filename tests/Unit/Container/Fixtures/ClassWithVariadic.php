<?php
declare(strict_types=1);

namespace Tests\Unit\Container\Fixtures;

final class ClassWithVariadic
{
    /** @var array<string> */
    public readonly array $items;

    public function __construct(
        public readonly string $name,
        string ...$items
    )
    {
        $this->items = $items;
    }
}
