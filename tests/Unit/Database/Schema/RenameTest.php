<?php
declare(strict_types=1);

namespace Tests\Unit\Database\Schema;

use Engine\Database\Schema\Rename;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('unit'), Group('database'), Group('rename')]
class RenameTest extends TestCase
{
    #[Test]
    public function renameTableGeneratesCorrectSql(): void
    {
        $this->assertSame(
            'RENAME TABLE `old` TO `new`',
            Rename::table('old', 'new')->toSql(),
        );
    }

    #[Test]
    public function getBindingsReturnsEmptyArray(): void
    {
        $this->assertSame([], Rename::table('old', 'new')->getBindings());
    }
}
