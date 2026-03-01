<?php
declare(strict_types=1);

namespace Tests\Unit\Container\Concerns;

use Engine\Container\Attributes\Named;
use Engine\Container\Concerns\HelpsWithReflection;
use Engine\Container\Dependency;
use Engine\Container\Exceptions\InvalidClassException;
use Engine\Container\Exceptions\InvalidMethodException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionFunction;
use ReflectionMethod;
use stdClass;
use Tests\Unit\Container\Fixtures\HasStaticMethod;
use Tests\Unit\Container\Fixtures\InvokableClass;
use Tests\Unit\Container\Fixtures\SimpleClass;
use Tests\Unit\Container\Fixtures\SimpleInterface;

final class HelpsWithReflectionTest extends TestCase
{
    private object $harness;

    protected function setUp(): void
    {
        $this->harness = new class {
            use HelpsWithReflection {
                isSingleType        as public;
                isIntersection      as public;
                isUnionType         as public;
                getTypeClassName    as public;
                getClassReflector   as public;
                getMethodReflector  as public;
                getFunctionReflector as public;
                getFunctionName     as public;
                getAttributeInstance as public;
            }
        };
    }

    // ── Type checking ────────────────────────────────────────────

    #[Test]
    public function is_single_type_returns_true_for_named_type(): void
    {
        $dependency = $this->makeDependencyWithType(static function (string $v): void {});

        $this->assertTrue($this->harness->isSingleType($dependency));
    }

    #[Test]
    public function is_single_type_returns_false_for_union_type(): void
    {
        $dependency = $this->makeDependencyWithType(static function (string|int $v): void {});

        $this->assertFalse($this->harness->isSingleType($dependency));
    }

    #[Test]
    public function is_single_type_returns_false_for_null_type(): void
    {
        $dependency = new Dependency('param', null);

        $this->assertFalse($this->harness->isSingleType($dependency));
    }

    #[Test]
    public function is_intersection_returns_true_for_intersection_type(): void
    {
        $dependency = $this->makeDependencyWithType(
            static function (SimpleInterface&\Countable $v): void {}
        );

        $this->assertTrue($this->harness->isIntersection($dependency));
    }

    #[Test]
    public function is_intersection_returns_false_for_named_type(): void
    {
        $dependency = $this->makeDependencyWithType(static function (string $v): void {});

        $this->assertFalse($this->harness->isIntersection($dependency));
    }

    #[Test]
    public function is_union_type_returns_true_for_union(): void
    {
        $dependency = $this->makeDependencyWithType(static function (string|int $v): void {});

        $this->assertTrue($this->harness->isUnionType($dependency));
    }

    #[Test]
    public function is_union_type_returns_false_for_named_type(): void
    {
        $dependency = $this->makeDependencyWithType(static function (string $v): void {});

        $this->assertFalse($this->harness->isUnionType($dependency));
    }

    #[Test]
    public function get_type_class_name_returns_name_for_single_type(): void
    {
        $dependency = $this->makeDependencyWithType(static function (string $v): void {});

        $this->assertSame('string', $this->harness->getTypeClassName($dependency));
    }

    #[Test]
    public function get_type_class_name_returns_null_for_union_type(): void
    {
        $dependency = $this->makeDependencyWithType(static function (string|int $v): void {});

        $this->assertNull($this->harness->getTypeClassName($dependency));
    }

    // ── Reflectors ───────────────────────────────────────────────

    #[Test]
    public function get_class_reflector_returns_reflection_class(): void
    {
        $reflector = $this->harness->getClassReflector(SimpleClass::class);

        $this->assertInstanceOf(ReflectionClass::class, $reflector);
        $this->assertSame(SimpleClass::class, $reflector->getName());
    }

    #[Test]
    public function get_class_reflector_throws_for_invalid_class(): void
    {
        $this->expectException(InvalidClassException::class);

        $this->harness->getClassReflector('NonExistent\\BadClass');
    }

    #[Test]
    public function get_method_reflector_returns_reflection_method(): void
    {
        $reflector = $this->harness->getMethodReflector(InvokableClass::class, '__invoke');

        $this->assertInstanceOf(ReflectionMethod::class, $reflector);
        $this->assertSame('__invoke', $reflector->getName());
    }

    #[Test]
    public function get_method_reflector_with_reflection_class(): void
    {
        $class     = new ReflectionClass(InvokableClass::class);
        $reflector = $this->harness->getMethodReflector($class, '__invoke');

        $this->assertInstanceOf(ReflectionMethod::class, $reflector);
        $this->assertSame('__invoke', $reflector->getName());
    }

    #[Test]
    public function get_method_reflector_throws_for_invalid_method(): void
    {
        $this->expectException(InvalidMethodException::class);

        $this->harness->getMethodReflector(SimpleClass::class, 'nonExistentMethod');
    }

    #[Test]
    public function get_function_reflector_returns_reflection_function(): void
    {
        $closure   = static fn () => 'hello';
        $reflector = $this->harness->getFunctionReflector($closure);

        $this->assertInstanceOf(\ReflectionFunction::class, $reflector);
    }

    // ── getFunctionName ──────────────────────────────────────────

    #[Test]
    public function get_function_name_returns_string_for_string_callable(): void
    {
        $name = $this->harness->getFunctionName('strlen');

        $this->assertSame('strlen', $name);
    }

    #[Test]
    public function get_function_name_returns_closure_format(): void
    {
        $closure = static fn () => null;
        $name    = $this->harness->getFunctionName($closure);

        $this->assertStringStartsWith('\\Closure{', $name);
        $this->assertStringEndsWith('}', $name);
    }

    #[Test]
    public function get_function_name_returns_invoke_for_invokable_object(): void
    {
        $object = new InvokableClass();
        $name   = $this->harness->getFunctionName($object);

        $this->assertSame(InvokableClass::class . '::__invoke', $name);
    }

    #[Test]
    public function get_function_name_returns_class_method_for_array_callable_with_object(): void
    {
        $object = new InvokableClass();
        $name   = $this->harness->getFunctionName([$object, '__invoke']);

        $this->assertSame(InvokableClass::class . '::__invoke', $name);
    }

    #[Test]
    public function get_function_name_returns_class_method_for_array_callable_with_class_string(): void
    {
        $name = $this->harness->getFunctionName([HasStaticMethod::class, 'greet']);

        $this->assertSame(HasStaticMethod::class . '::greet', $name);
    }

    // ── getAttributeInstance ─────────────────────────────────────

    #[Test]
    public function get_attribute_instance_returns_attribute_when_present(): void
    {
        // Create a function with a #[Named] parameter
        $fn    = new ReflectionFunction(static function (#[Named('test')] string $v): void {});
        $param = $fn->getParameters()[0];

        $attr = $this->harness->getAttributeInstance($param, Named::class);

        $this->assertInstanceOf(Named::class, $attr);
        $this->assertSame('test', $attr->name);
    }

    #[Test]
    public function get_attribute_instance_returns_null_when_not_present(): void
    {
        $fn    = new ReflectionFunction(static function (string $v): void {});
        $param = $fn->getParameters()[0];

        $attr = $this->harness->getAttributeInstance($param, Named::class);

        $this->assertNull($attr);
    }

    // ── Helpers ──────────────────────────────────────────────────

    private function makeDependencyWithType(\Closure $fn): Dependency
    {
        $ref  = new ReflectionFunction($fn);
        $type = $ref->getParameters()[0]->getType();

        return new Dependency('param', $type);
    }
}
