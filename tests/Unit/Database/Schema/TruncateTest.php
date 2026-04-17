<?php
declare(strict_types=1);

namespace Tests\Unit\Database\Schema;

use Engine\Database\Schema\Truncate;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('unit'), Group('database'), Group('truncate')]
class TruncateTest extends TestCase
{
    #[Test]
    public function truncateTableGeneratesCorrectSql(): void
    {
        $this->assertSame(
            'TRUNCATE TABLE `users`',
            Truncate::table('users')->toSql(),
        );
    }

    #[Test]
    public function getBindingsReturnsEmptyArray(): void
    {
        $this->assertSame([], Truncate::table('users')->getBindings());
    }
}
