<?php
declare(strict_types=1);

namespace Engine\Container\Attributes;

use Attribute;
use Engine\Container\Contracts\Resolvable;

/**
 * NoResolution Attribute
 * ----------------------
 *
 * Marks a class as non-auto-resolvable. The container will throw an
 * UnresolvableClassException if it encounters this class during auto-wiring,
 * preventing unintended automatic instantiation.
 */
#[Attribute(Attribute::TARGET_PARAMETER | Attribute::TARGET_CLASS)]
final readonly class NoResolution implements Resolvable
{
}
