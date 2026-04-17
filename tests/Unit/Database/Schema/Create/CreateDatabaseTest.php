<?php
declare(strict_types=1);

namespace Tests\Unit\Database\Schema\Create;

use Engine\Database\Schema\Create;
use Engine\Database\Schema\Create\CreateDatabase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('unit'), Group('database'), Group('create-database')]
class CreateDatabaseTest extends TestCase
{
    #[Test]
    public function basicCreateDatabase(): void
    {
        $schema = new CreateDatabase('my_database');

        $this->assertSame('CREATE DATABASE `my_database`', $schema->toSql());
    }

    #[Test]
    public function createDatabaseIfNotExists(): void
    {
        $schema = new CreateDatabase('my_database');
        $schema->ifNotExists();

        $this->assertSame('CREATE DATABASE IF NOT EXISTS `my_database`', $schema->toSql());
    }

    #[Test]
    public function createDatabaseWithCharset(): void
    {
        $schema = new CreateDatabase('my_database');
        $schema->charset('utf8mb4');

        $this->assertSame('CREATE DATABASE `my_database` CHARACTER SET utf8mb4', $schema->toSql());
    }

    #[Test]
    public function createDatabaseWithCollation(): void
    {
        $schema = new CreateDatabase('my_database');
        $schema->collation('utf8mb4_unicode_ci');

        $this->assertSame('CREATE DATABASE `my_database` COLLATE utf8mb4_unicode_ci', $schema->toSql());
    }

    #[Test]
    public function createDatabaseWithAllOptions(): void
    {
        $schema = new CreateDatabase('my_database');
        $schema->ifNotExists()->charset('utf8mb4')->collation('utf8mb4_unicode_ci');

        $this->assertSame(
            'CREATE DATABASE IF NOT EXISTS `my_database` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci',
            $schema->toSql(),
        );
    }

    #[Test]
    public function bindingsAreEmpty(): void
    {
        $schema = new CreateDatabase('my_database');

        $this->assertSame([], $schema->getBindings());
    }

    #[Test]
    public function factoryMethodCreatesInstance(): void
    {
        $schema = Create::database('my_database');

        $this->assertInstanceOf(CreateDatabase::class, $schema);
    }
}
