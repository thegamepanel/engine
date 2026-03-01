<?php
declare(strict_types=1);

namespace Tests\Unit\Values;

use DateTimeImmutable;
use Engine\Values\Timestamps;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class TimestampsTest extends TestCase
{
    // ── Constructor ───────────────────────────────────────────

    #[Test]
    public function it_creates_empty_timestamps(): void
    {
        $ts = new Timestamps();

        $this->assertFalse($ts->has('created_at'));
    }

    #[Test]
    public function it_stores_named_arguments(): void
    {
        $now = new DateTimeImmutable();
        $ts  = new Timestamps(created_at: $now);

        $this->assertTrue($ts->has('created_at'));
        $this->assertSame($now, $ts->get('created_at'));
    }

    #[Test]
    public function it_stores_multiple_named_arguments(): void
    {
        $created = new DateTimeImmutable('2025-01-01');
        $updated = new DateTimeImmutable('2025-06-15');

        $ts = new Timestamps(created_at: $created, updated_at: $updated);

        $this->assertSame($created, $ts->get('created_at'));
        $this->assertSame($updated, $ts->get('updated_at'));
    }

    #[Test]
    public function it_stores_null_values_from_named_arguments(): void
    {
        $ts = new Timestamps(deleted_at: null);

        $this->assertTrue($ts->has('deleted_at'));
        $this->assertNull($ts->get('deleted_at'));
    }

    #[Test]
    public function it_throws_on_positional_arguments(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Timestamps(new DateTimeImmutable());
    }

    // ── get() ─────────────────────────────────────────────────

    #[Test]
    public function get_returns_timestamp(): void
    {
        $now = new DateTimeImmutable();
        $ts  = new Timestamps(created_at: $now);

        $this->assertSame($now, $ts->get('created_at'));
    }

    #[Test]
    public function get_returns_null_for_non_existent(): void
    {
        $ts = new Timestamps();

        $this->assertNull($ts->get('created_at'));
    }

    #[Test]
    public function get_returns_null_for_null_valued_timestamp(): void
    {
        $ts = new Timestamps(deleted_at: null);

        $this->assertNull($ts->get('deleted_at'));
    }

    // ── has() ─────────────────────────────────────────────────

    #[Test]
    public function has_returns_true_for_existing(): void
    {
        $ts = new Timestamps(created_at: new DateTimeImmutable());

        $this->assertTrue($ts->has('created_at'));
    }

    #[Test]
    public function has_returns_false_for_non_existent(): void
    {
        $ts = new Timestamps();

        $this->assertFalse($ts->has('created_at'));
    }

    #[Test]
    public function has_returns_true_for_null_valued_timestamp(): void
    {
        $ts = new Timestamps(deleted_at: null);

        // Uses array_key_exists, not isset — null values are "present"
        $this->assertTrue($ts->has('deleted_at'));
    }

    // ── add() ─────────────────────────────────────────────────

    #[Test]
    public function add_stores_new_timestamp(): void
    {
        $ts  = new Timestamps();
        $now = new DateTimeImmutable();

        $ts->add('created_at', $now);

        $this->assertSame($now, $ts->get('created_at'));
    }

    #[Test]
    public function add_does_not_overwrite_existing(): void
    {
        $original = new DateTimeImmutable('2025-01-01');
        $newer    = new DateTimeImmutable('2025-12-31');

        $ts = new Timestamps(created_at: $original);
        $ts->add('created_at', $newer);

        $this->assertSame($original, $ts->get('created_at'));
    }

    #[Test]
    public function add_returns_self(): void
    {
        $ts = new Timestamps();

        $this->assertSame($ts, $ts->add('created_at', new DateTimeImmutable()));
    }

    // ── set() ─────────────────────────────────────────────────

    #[Test]
    public function set_overwrites_existing(): void
    {
        $original = new DateTimeImmutable('2025-01-01');
        $newer    = new DateTimeImmutable('2025-12-31');

        $ts = new Timestamps(created_at: $original);
        $ts->set('created_at', $newer);

        $this->assertSame($newer, $ts->get('created_at'));
    }

    #[Test]
    public function set_returns_self(): void
    {
        $ts = new Timestamps();

        $this->assertSame($ts, $ts->set('created_at', new DateTimeImmutable()));
    }
}
