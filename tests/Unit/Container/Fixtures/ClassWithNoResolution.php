<?php
declare(strict_types=1);

namespace Tests\Unit\Container\Fixtures;

use Engine\Container\Attributes\NoResolution;

/**
 * A fixture class decorated with {@see NoResolution} to support testing that the
 * container refuses to auto-wire the class and instead throws an
 * UnresolvableClassException, even though the class is otherwise valid.
 */
#[NoResolution]
class ClassWithNoResolution
{
}
