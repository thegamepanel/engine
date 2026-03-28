<?php
declare(strict_types=1);

namespace Tests\Unit\Container\Fixtures;

use Countable;
use Stringable;

/**
 * A fixture class implementing both Stringable and Countable. Used as the concrete
 * type for intersection type resolution tests, where the container must find a binding
 * whose resolved instance satisfies every interface in the intersection.
 */
class StringableCountable implements Stringable, Countable
{
    public function __toString(): string
    {
        return '';
    }

    public function count(): int
    {
        return 0;
    }
}
