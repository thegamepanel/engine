<?php
declare(strict_types=1);

namespace Tests\Unit\Container;

use Engine\Container\Attributes\Named;
use Engine\Container\Contracts\Resolvable;
use Engine\Container\Dependency;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionFunction;
use ReflectionNamedType;
use ReflectionUnionType;
use Tests\Unit\Container\Fixtures\TestQualifier;

final class DependencyTest extends TestCase
{
    #[Test]
    public function it_stores_all_properties(): void
    {
        $named      = new Named('primary');
        $qualifier  = new TestQualifier();
        $type       = $this->getNamedType('string');

        $dependency = new Dependency(
            parameter:  'myParam',
            type:       $type,
            optional:   true,
            name:       $named,
            qualifier:  $qualifier,
            resolvable: null,
            hasDefault: true,
            default:    'fallback',
            liminal:    true,
        );

        $this->assertSame('myParam', $dependency->parameter);
        $this->assertSame($type, $dependency->type);
        $this->assertTrue($dependency->optional);
        $this->assertSame($named, $dependency->name);
        $this->assertSame($qualifier, $dependency->qualifier);
        $this->assertNull($dependency->resolvable);
        $this->assertTrue($dependency->hasDefault);
        $this->assertSame('fallback', $dependency->default);
        $this->assertTrue($dependency->liminal);
    }

    #[Test]
    public function it_has_sensible_defaults(): void
    {
        $dependency = new Dependency(
            parameter: 'myParam',
            type:      null,
        );

        $this->assertFalse($dependency->optional);
        $this->assertNull($dependency->name);
        $this->assertNull($dependency->qualifier);
        $this->assertNull($dependency->resolvable);
        $this->assertFalse($dependency->hasDefault);
        $this->assertNull($dependency->default);
        $this->assertFalse($dependency->liminal);
    }

    #[Test]
    public function it_accepts_named_type(): void
    {
        $type       = $this->getNamedType('string');
        $dependency = new Dependency('param', $type);

        $this->assertInstanceOf(ReflectionNamedType::class, $dependency->type);
    }

    #[Test]
    public function it_accepts_union_type(): void
    {
        $fn   = new ReflectionFunction(static function (string|int $v): mixed { return $v; });
        $type = $fn->getParameters()[0]->getType();

        $dependency = new Dependency('param', $type);

        $this->assertInstanceOf(ReflectionUnionType::class, $dependency->type);
    }

    #[Test]
    public function it_accepts_null_type(): void
    {
        $dependency = new Dependency('param', null);

        $this->assertNull($dependency->type);
    }

    private function getNamedType(string $typeName): ReflectionNamedType
    {
        $fn = match ($typeName) {
            'string' => new ReflectionFunction(static function (string $v): mixed { return $v; }),
            'int'    => new ReflectionFunction(static function (int $v): mixed { return $v; }),
            default  => new ReflectionFunction(static function (mixed $v): mixed { return $v; }),
        };

        /** @var ReflectionNamedType */
        return $fn->getParameters()[0]->getType();
    }
}
