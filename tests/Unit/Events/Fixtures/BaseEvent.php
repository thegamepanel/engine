<?php
declare(strict_types=1);

namespace Tests\Unit\Events\Fixtures;

class BaseEvent
{
    public function __construct(
        public readonly string $name,
    ) {
    }
}
