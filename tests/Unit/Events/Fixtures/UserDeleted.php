<?php
declare(strict_types=1);

namespace Tests\Unit\Events\Fixtures;

final class UserDeleted
{
    public function __construct(
        public readonly int $id,
    ) {
    }
}
