<?php
declare(strict_types=1);

namespace Tests\Unit\Container\Fixtures;

use Engine\Container\Contracts\Qualifier;

/**
 * A value-based qualifier that uses a string tag to distinguish instances of the
 * same qualifier class. Two TaggedQualifier instances are equal only when their
 * tags match. Used to test that the container's qualified instance cache uses
 * both the qualifier class check (===) AND the equality check (equals()) when
 * looking up cached instances, not just one of the two conditions.
 */
class TaggedQualifier implements Qualifier
{
    public function __construct(public readonly string $tag)
    {
    }

    public function equals(Qualifier $other): bool
    {
        return $other instanceof self && $other->tag === $this->tag;
    }
}
