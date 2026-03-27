<?php
declare(strict_types=1);

namespace Tests\Unit\Container\Fixtures;

use Engine\Container\Attributes\Liminal;

/**
 * A fixture class decorated with {@see Liminal} to support testing that the class-level
 * attribute causes the container to store resolved instances as weak references rather
 * than strong shared references.
 */
#[Liminal]
class LiminalClass
{
    //
}
