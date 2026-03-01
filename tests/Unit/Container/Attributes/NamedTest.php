<?php
declare(strict_types=1);

namespace Tests\Unit\Container\Attributes;

use Attribute;
use Engine\Container\Attributes\Named;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class NamedTest extends TestCase
{
    #[Test]
    public function it_is_instantiable_with_a_name(): void
    {
        $named = new Named('primary');

        $this->assertInstanceOf(Named::class, $named);
    }

    #[Test]
    public function it_stores_the_name(): void
    {
        $named = new Named('primary');

        $this->assertSame('primary', $named->name);
    }

    #[Test]
    public function it_targets_parameters_only(): void
    {
        $reflector  = new ReflectionClass(Named::class);
        $attributes = $reflector->getAttributes(Attribute::class);

        $this->assertNotEmpty($attributes);

        $attribute = $attributes[0]->newInstance();

        $this->assertSame(Attribute::TARGET_PARAMETER, $attribute->flags);
    }
}
