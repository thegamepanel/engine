<?php
declare(strict_types=1);

namespace Tests\Unit\Container\Attributes;

use Attribute;
use Engine\Container\Attributes\Ghost;
use Engine\Container\Contracts\Resolvable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class GhostTest extends TestCase
{
    #[Test]
    public function it_is_instantiable(): void
    {
        $ghost = new Ghost();

        $this->assertInstanceOf(Ghost::class, $ghost);
    }

    #[Test]
    public function it_implements_resolvable(): void
    {
        $ghost = new Ghost();

        $this->assertInstanceOf(Resolvable::class, $ghost);
    }

    #[Test]
    public function it_targets_parameters_only(): void
    {
        $reflector  = new ReflectionClass(Ghost::class);
        $attributes = $reflector->getAttributes(Attribute::class);

        $this->assertNotEmpty($attributes);

        $attribute = $attributes[0]->newInstance();

        $this->assertSame(Attribute::TARGET_PARAMETER, $attribute->flags);
    }
}
