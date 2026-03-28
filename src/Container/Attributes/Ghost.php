<?php
declare(strict_types=1);

namespace Engine\Container\Attributes;

use Attribute;
use Engine\Container\Contracts\Resolvable;

/**
 * Ghost Attribute
 * ---------------
 *
 * Marks a parameter for lazy ghost resolution. The container will create an
 * uninitialized lazy ghost object whose constructor is deferred until the
 * object is first accessed.
 */
#[Attribute(Attribute::TARGET_PARAMETER)]
final readonly class Ghost implements Resolvable
{
}
