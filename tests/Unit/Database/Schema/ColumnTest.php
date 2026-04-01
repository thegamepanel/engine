<?php
declare(strict_types=1);

namespace Tests\Unit\Database\Schema;

use Engine\Database\Query\Raw;
use Engine\Database\Schema\Column;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('unit'), Group('database'), Group('schema'), Group('column')]
class ColumnTest extends TestCase
{
    // -------------------------------------------------------------------------
    // Numeric type factories
    // -------------------------------------------------------------------------

    /**
     * - bigInt produces BIGINT column definition.
     */
    #[Test]
    public function bigIntProducesCorrectSql(): void
    {
        $column = Column::bigInt('id');

        $this->assertSame('`id` BIGINT NOT NULL', $column->toSql());
        $this->assertSame([], $column->getBindings());
    }

    /**
     * - int produces INT column definition.
     */
    #[Test]
    public function intProducesCorrectSql(): void
    {
        $column = Column::int('age');

        $this->assertSame('`age` INT NOT NULL', $column->toSql());
        $this->assertSame([], $column->getBindings());
    }

    /**
     * - smallInt produces SMALLINT column definition.
     */
    #[Test]
    public function smallIntProducesCorrectSql(): void
    {
        $column = Column::smallInt('count');

        $this->assertSame('`count` SMALLINT NOT NULL', $column->toSql());
        $this->assertSame([], $column->getBindings());
    }

    /**
     * - tinyInt produces TINYINT column definition.
     */
    #[Test]
    public function tinyIntProducesCorrectSql(): void
    {
        $column = Column::tinyInt('flag');

        $this->assertSame('`flag` TINYINT NOT NULL', $column->toSql());
        $this->assertSame([], $column->getBindings());
    }

    /**
     * - decimal produces DECIMAL(precision, scale) column definition.
     */
    #[Test]
    public function decimalProducesCorrectSql(): void
    {
        $column = Column::decimal('price', 10, 2);

        $this->assertSame('`price` DECIMAL(10, 2) NOT NULL', $column->toSql());
        $this->assertSame([], $column->getBindings());
    }

    /**
     * - float produces FLOAT column definition.
     */
    #[Test]
    public function floatProducesCorrectSql(): void
    {
        $column = Column::float('rating');

        $this->assertSame('`rating` FLOAT NOT NULL', $column->toSql());
        $this->assertSame([], $column->getBindings());
    }

    /**
     * - double produces DOUBLE column definition.
     */
    #[Test]
    public function doubleProducesCorrectSql(): void
    {
        $column = Column::double('precise');

        $this->assertSame('`precise` DOUBLE NOT NULL', $column->toSql());
        $this->assertSame([], $column->getBindings());
    }

    // -------------------------------------------------------------------------
    // String type factories
    // -------------------------------------------------------------------------

    /**
     * - string produces VARCHAR(length) column definition.
     */
    #[Test]
    public function stringProducesCorrectSql(): void
    {
        $column = Column::string('name', 255);

        $this->assertSame('`name` VARCHAR(255) NOT NULL', $column->toSql());
        $this->assertSame([], $column->getBindings());
    }

    /**
     * - char() produces the correct SQL with name and length.
     */
    #[Test]
    public function charProducesCorrectSql(): void
    {
        $column = Column::char('code', 2);

        $this->assertSame('`code` CHAR(2) NOT NULL', $column->toSql());
        $this->assertSame([], $column->getBindings());
    }

    /**
     * - text produces TEXT column definition.
     */
    #[Test]
    public function textProducesCorrectSql(): void
    {
        $column = Column::text('body');

        $this->assertSame('`body` TEXT NOT NULL', $column->toSql());
        $this->assertSame([], $column->getBindings());
    }

    /**
     * - mediumText produces MEDIUMTEXT column definition.
     */
    #[Test]
    public function mediumTextProducesCorrectSql(): void
    {
        $column = Column::mediumText('content');

        $this->assertSame('`content` MEDIUMTEXT NOT NULL', $column->toSql());
        $this->assertSame([], $column->getBindings());
    }

    /**
     * - longText produces LONGTEXT column definition.
     */
    #[Test]
    public function longTextProducesCorrectSql(): void
    {
        $column = Column::longText('data');

        $this->assertSame('`data` LONGTEXT NOT NULL', $column->toSql());
        $this->assertSame([], $column->getBindings());
    }

    // -------------------------------------------------------------------------
    // Date/time type factories
    // -------------------------------------------------------------------------

    /**
     * - date produces DATE column definition.
     */
    #[Test]
    public function dateProducesCorrectSql(): void
    {
        $column = Column::date('birthday');

        $this->assertSame('`birthday` DATE NOT NULL', $column->toSql());
        $this->assertSame([], $column->getBindings());
    }

    /**
     * - datetime produces DATETIME column definition.
     */
    #[Test]
    public function datetimeProducesCorrectSql(): void
    {
        $column = Column::datetime('published_at');

        $this->assertSame('`published_at` DATETIME NOT NULL', $column->toSql());
        $this->assertSame([], $column->getBindings());
    }

    /**
     * - timestamp produces TIMESTAMP column definition.
     */
    #[Test]
    public function timestampProducesCorrectSql(): void
    {
        $column = Column::timestamp('created_at');

        $this->assertSame('`created_at` TIMESTAMP NOT NULL', $column->toSql());
        $this->assertSame([], $column->getBindings());
    }

    /**
     * - time produces TIME column definition.
     */
    #[Test]
    public function timeProducesCorrectSql(): void
    {
        $column = Column::time('duration');

        $this->assertSame('`duration` TIME NOT NULL', $column->toSql());
        $this->assertSame([], $column->getBindings());
    }

    // -------------------------------------------------------------------------
    // Binary/blob type factories
    // -------------------------------------------------------------------------

    /**
     * - binary produces BINARY column definition.
     */
    #[Test]
    public function binaryProducesCorrectSql(): void
    {
        $column = Column::binary('hash');

        $this->assertSame('`hash` BINARY NOT NULL', $column->toSql());
        $this->assertSame([], $column->getBindings());
    }

    /**
     * - blob produces BLOB column definition.
     */
    #[Test]
    public function blobProducesCorrectSql(): void
    {
        $column = Column::blob('data');

        $this->assertSame('`data` BLOB NOT NULL', $column->toSql());
        $this->assertSame([], $column->getBindings());
    }

    // -------------------------------------------------------------------------
    // JSON / boolean / enum type factories
    // -------------------------------------------------------------------------

    /**
     * - json produces JSON column definition.
     */
    #[Test]
    public function jsonProducesCorrectSql(): void
    {
        $column = Column::json('metadata');

        $this->assertSame('`metadata` JSON NOT NULL', $column->toSql());
        $this->assertSame([], $column->getBindings());
    }

    /**
     * - boolean produces BOOLEAN column definition.
     */
    #[Test]
    public function booleanProducesCorrectSql(): void
    {
        $column = Column::boolean('active');

        $this->assertSame('`active` BOOLEAN NOT NULL', $column->toSql());
        $this->assertSame([], $column->getBindings());
    }

    /**
     * - enum produces ENUM column definition with quoted values.
     */
    #[Test]
    public function enumProducesCorrectSql(): void
    {
        $column = Column::enum('status', ['active', 'inactive', 'pending']);

        $this->assertSame("`status` ENUM('active', 'inactive', 'pending') NOT NULL", $column->toSql());
        $this->assertSame([], $column->getBindings());
    }

    /**
     * - enum escapes single quotes in values.
     */
    #[Test]
    public function enumEscapesSingleQuotesInValues(): void
    {
        $column = Column::enum('label', ["it's", "they're"]);

        $this->assertSame("`label` ENUM('it''s', 'they''re') NOT NULL", $column->toSql());
    }

    // -------------------------------------------------------------------------
    // Modifiers
    // -------------------------------------------------------------------------

    /**
     * - nullable() produces NULL instead of NOT NULL.
     */
    #[Test]
    public function nullableProducesNullSql(): void
    {
        $column = Column::string('email', 255)->nullable();

        $this->assertSame('`email` VARCHAR(255) NULL', $column->toSql());
    }

    /**
     * - default() with a string value produces DEFAULT 'value'.
     */
    #[Test]
    public function defaultWithStringProducesCorrectSql(): void
    {
        $column = Column::string('status', 20)->default('active');

        $this->assertSame("`status` VARCHAR(20) NOT NULL DEFAULT 'active'", $column->toSql());
    }

    /**
     * - default() with null produces DEFAULT NULL.
     */
    #[Test]
    public function defaultWithNullProducesCorrectSql(): void
    {
        $column = Column::string('bio', 500)->nullable()->default(null);

        $this->assertSame('`bio` VARCHAR(500) NULL DEFAULT NULL', $column->toSql());
    }

    /**
     * - default() with an integer produces DEFAULT N.
     */
    #[Test]
    public function defaultWithIntProducesCorrectSql(): void
    {
        $column = Column::int('count')->default(0);

        $this->assertSame('`count` INT NOT NULL DEFAULT 0', $column->toSql());
    }

    /**
     * - default() with a boolean produces DEFAULT TRUE/FALSE.
     */
    #[Test]
    public function defaultWithBoolProducesCorrectSql(): void
    {
        $column = Column::boolean('active')->default(true);

        $this->assertSame('`active` BOOLEAN NOT NULL DEFAULT TRUE', $column->toSql());

        $column = Column::boolean('deleted')->default(false);

        $this->assertSame('`deleted` BOOLEAN NOT NULL DEFAULT FALSE', $column->toSql());
    }

    /**
     * - default() with a float value produces DEFAULT N.N.
     */
    #[Test]
    public function defaultWithFloatProducesCorrectSql(): void
    {
        $column = Column::float('rating')->default(3.14);

        $this->assertSame('`rating` FLOAT NOT NULL DEFAULT 3.14', $column->toSql());
    }

    /**
     * - default() with an Expression renders it inline.
     */
    #[Test]
    public function defaultWithExpressionRendersInline(): void
    {
        $column = Column::timestamp('created_at')->default(
            Raw::from('CURRENT_TIMESTAMP'),
        );

        $this->assertSame('`created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP', $column->toSql());
    }

    /**
     * - default() with a string containing a single quote escapes it.
     */
    #[Test]
    public function defaultWithSingleQuoteEscapesCorrectly(): void
    {
        $column = Column::string('greeting', 100)->default("it's");

        $this->assertSame("`greeting` VARCHAR(100) NOT NULL DEFAULT 'it''s'", $column->toSql());
    }

    /**
     * - unsigned() produces UNSIGNED before NULL/NOT NULL.
     */
    #[Test]
    public function unsignedProducesCorrectSql(): void
    {
        $column = Column::bigInt('id')->unsigned();

        $this->assertSame('`id` BIGINT UNSIGNED NOT NULL', $column->toSql());
    }

    /**
     * - autoIncrement() produces AUTO_INCREMENT after NULL/NOT NULL.
     */
    #[Test]
    public function autoIncrementProducesCorrectSql(): void
    {
        $column = Column::bigInt('id')->unsigned()->autoIncrement();

        $this->assertSame('`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT', $column->toSql());
    }

    /**
     * - comment() produces COMMENT with escaped string.
     */
    #[Test]
    public function commentProducesCorrectSql(): void
    {
        $column = Column::string('name', 255)->comment('The user name');

        $this->assertSame("`name` VARCHAR(255) NOT NULL COMMENT 'The user name'", $column->toSql());
    }

    /**
     * - comment() with a single quote escapes it.
     */
    #[Test]
    public function commentWithSingleQuoteEscapesCorrectly(): void
    {
        $column = Column::string('name', 255)->comment("user's name");

        $this->assertSame("`name` VARCHAR(255) NOT NULL COMMENT 'user''s name'", $column->toSql());
    }

    /**
     * - charset() produces CHARACTER SET clause.
     */
    #[Test]
    public function charsetProducesCorrectSql(): void
    {
        $column = Column::string('name', 255)->charset('utf8mb4');

        $this->assertSame('`name` VARCHAR(255) NOT NULL CHARACTER SET utf8mb4', $column->toSql());
    }

    /**
     * - collation() produces COLLATE clause.
     */
    #[Test]
    public function collationProducesCorrectSql(): void
    {
        $column = Column::string('name', 255)->collation('utf8mb4_unicode_ci');

        $this->assertSame('`name` VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci', $column->toSql());
    }

    /**
     * - after() produces AFTER clause.
     */
    #[Test]
    public function afterProducesCorrectSql(): void
    {
        $column = Column::string('bio', 500)->nullable()->after('email');

        $this->assertSame('`bio` VARCHAR(500) NULL AFTER `email`', $column->toSql());
    }

    /**
     * - first() produces FIRST clause.
     */
    #[Test]
    public function firstProducesCorrectSql(): void
    {
        $column = Column::string('id_col', 36)->first();

        $this->assertSame('`id_col` VARCHAR(36) NOT NULL FIRST', $column->toSql());
    }

    /**
     * - Multiple modifiers combine in the correct order.
     */
    #[Test]
    public function multipleModifiersCombineCorrectly(): void
    {
        $column = Column::string('email', 255)
            ->nullable()
            ->default('test@example.com')
            ->comment('Primary email')
            ->charset('utf8mb4')
            ->collation('utf8mb4_unicode_ci')
            ->after('name')
        ;

        $this->assertSame(
            "`email` VARCHAR(255) NULL DEFAULT 'test@example.com' COMMENT 'Primary email'"
            . ' CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci AFTER `name`',
            $column->toSql(),
        );
    }

    /**
     * - Unsigned auto-increment column produces correct SQL order.
     */
    #[Test]
    public function unsignedAutoIncrementProducesCorrectSqlOrder(): void
    {
        $column = Column::bigInt('id')
            ->unsigned()
            ->autoIncrement()
            ->comment('Primary key')
            ->first()
        ;

        $this->assertSame(
            "`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Primary key' FIRST",
            $column->toSql(),
        );
    }

    // -------------------------------------------------------------------------
    // Named reference
    // -------------------------------------------------------------------------

    /**
     * - named() produces just the backtick-quoted column name.
     */
    #[Test]
    public function namedProducesQuotedName(): void
    {
        $column = Column::named('email');

        $this->assertSame('`email`', $column->toSql());
        $this->assertSame([], $column->getBindings());
    }
}
