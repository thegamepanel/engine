<?php
declare(strict_types=1);

namespace Tests\Unit\Container\Attributes;

use Attribute;
use Engine\Container\Attributes\Liminal;
use Engine\Container\Contracts\Resolvable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class LiminalTest extends TestCase
{
    #[Test]
    public function it_is_instantiable(): void
    {
        $liminal = new Liminal();

        $this->assertInstanceOf(Liminal::class, $liminal);
    }

    #[Test]
    public function it_implements_resolvable(): void
    {
        $liminal = new Liminal();

        $this->assertInstanceOf(Resolvable::class, $liminal);
    }

    #[Test]
    public function it_targets_parameters_and_classes(): void
    {
        $reflector  = new ReflectionClass(Liminal::class);
        $attributes = $reflector->getAttributes(Attribute::class);

        $this->assertNotEmpty($attributes);

        $attribute = $attributes[0]->newInstance();

        $this->assertSame(Attribute::TARGET_PARAMETER | Attribute::TARGET_CLASS, $attribute->flags);
    }
}
