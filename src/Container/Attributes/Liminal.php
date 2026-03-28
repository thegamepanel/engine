<?php
declare(strict_types=1);

namespace Engine\Container\Attributes;

use Attribute;
use Engine\Container\Contracts\Resolvable;

/**
 * Liminal Attribute
 * -----------------
 *
 * Marks a parameter or class as liminal, meaning the resolved instance is
 * held as a weak reference. The instance is eligible for garbage collection
 * once all other strong references are released.
 */
#[Attribute(Attribute::TARGET_PARAMETER | Attribute::TARGET_CLASS)]
final readonly class Liminal implements Resolvable
{
}
