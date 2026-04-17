<?php
declare(strict_types=1);

namespace Tests\Unit\Database\Schema\Alter;

use Engine\Database\Schema\Alter;
use Engine\Database\Schema\Alter\AlterDatabase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('unit'), Group('database'), Group('alter-database')]
class AlterDatabaseTest extends TestCase
{
    #[Test]
    public function alterDatabaseWithCharset(): void
    {
        $schema = new AlterDatabase('my_database');
        $schema->charset('utf8mb4');

        $this->assertSame('ALTER DATABASE `my_database` CHARACTER SET utf8mb4', $schema->toSql());
    }

    #[Test]
    public function alterDatabaseWithCollation(): void
    {
        $schema = new AlterDatabase('my_database');
        $schema->collation('utf8mb4_unicode_ci');

        $this->assertSame('ALTER DATABASE `my_database` COLLATE utf8mb4_unicode_ci', $schema->toSql());
    }

    #[Test]
    public function alterDatabaseWithCharsetAndCollation(): void
    {
        $schema = new AlterDatabase('my_database');
        $schema->charset('utf8mb4')->collation('utf8mb4_unicode_ci');

        $this->assertSame(
            'ALTER DATABASE `my_database` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci',
            $schema->toSql(),
        );
    }

    #[Test]
    public function bindingsAreEmpty(): void
    {
        $schema = new AlterDatabase('my_database');

        $this->assertSame([], $schema->getBindings());
    }

    #[Test]
    public function factoryMethodCreatesInstance(): void
    {
        $schema = Alter::database('my_database');

        $this->assertInstanceOf(AlterDatabase::class, $schema);
    }
}
