<?php
declare(strict_types=1);

namespace Tests\Unit\Events\Attributes;

use Attribute;
use Engine\Events\Attributes\Listener;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class ListenerTest extends TestCase
{
    #[Test]
    public function it_is_instantiable(): void
    {
        $listener = new Listener();

        $this->assertInstanceOf(Listener::class, $listener);
    }

    #[Test]
    public function it_targets_methods_only(): void
    {
        $ref   = new ReflectionClass(Listener::class);
        $attrs = $ref->getAttributes(Attribute::class);

        $this->assertNotEmpty($attrs);

        $instance = $attrs[0]->newInstance();

        $this->assertSame(Attribute::TARGET_METHOD, $instance->flags);
    }

    #[Test]
    public function it_has_no_constructor_parameters(): void
    {
        $ref = new ReflectionClass(Listener::class);

        $this->assertNull($ref->getConstructor());
    }
}
