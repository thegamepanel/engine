<?php
declare(strict_types=1);

namespace Tests\Unit\Events\Fixtures;

final class UserCreated
{
    public function __construct(
        public string $name,
    ) {
    }
}
