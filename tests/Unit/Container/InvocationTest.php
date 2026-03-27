<?php
declare(strict_types=1);

namespace Tests\Unit\Container;

use Engine\Container\Invocation;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Unit\Container\Fixtures\ClassWithMethods;

#[Group('unit'), Group('container'), Group('invocation')]
class InvocationTest extends TestCase
{
    /**
     * @return array<string, array{callable}>
     */
    public static function callableDataProvider(): array
    {
        return [
            'short function'  => [static fn () => 'foo'],
            'long function'   => [
                function () {
                    return 'foo';
                },
            ],
            'callable string' => ['strlen'],
            'object method'   => [[new ClassWithMethods(), 'callableMethod']],
            'static method'   => [[ClassWithMethods::class, 'callableStaticMethod']],
        ];
    }

    /**
     * @return array<string, array{string|object, string}>
     */
    public static function methodDataProvider(): array
    {
        return [
            'class string' => [ClassWithMethods::class, 'callableMethod'],
            'object'       => [new ClassWithMethods(), 'callableMethod'],
        ];
    }

    /**
     * @return array<string, array{string|object}>
     */
    public static function constructorDataProvider(): array
    {
        return [
            'class string' => [ClassWithMethods::class],
            'object'       => [new ClassWithMethods()],
        ];
    }

    /**
     * @return array<string, array{string|object, string, array<string, string>}>
     */
    public static function withArgumentsDataProvider(): array
    {
        return [
            'class string' => [ClassWithMethods::class, 'callableMethod', ['arg1' => 'value1', 'arg2' => 'value2']],
            'object'       => [new ClassWithMethods(), 'callableMethod', ['arg1' => 'value1', 'arg2' => 'value2']],
        ];
    }

    /**
     * - A callable invocation has no class reference and carries the callable as its
     *   invokable, covering closures, named functions, and array callables.
     */
    #[Test, DataProvider('callableDataProvider')]
    public function createsCallableRepresentationSuccessfully(callable $callable): void
    {
        $instance = Invocation::callable($callable);

        $this->assertTrue($instance->isCallable());
        $this->assertFalse($instance->isClassMethodCall());
        $this->assertNull($instance->class);
        $this->assertIsCallable($instance->invokable);
        $this->assertEmpty($instance->arguments);
    }

    /**
     * - A method-call invocation carries both a class/object target and a method name,
     *   supporting both class-string and object forms of the target.
     */
    #[Test, DataProvider('methodDataProvider')]
    public function createsMethodCallRepresentationSuccessfully(string|object $class, string $method): void
    {
        $instance = Invocation::method($class, $method);

        $this->assertTrue($instance->isClassMethodCall());
        $this->assertFalse($instance->isCallable());
        $this->assertNotNull($instance->class);
        $this->assertSame($method, $instance->invokable);
        $this->assertEmpty($instance->arguments);
    }

    /**
     * - A constructor invocation is a specialised method call targeting `__construct`,
     *   accepting both class-string and object forms of the target.
     */
    #[Test, DataProvider('constructorDataProvider')]
    public function createsConstructorCallRepresentationSuccessfully(string|object $class): void
    {
        $instance = Invocation::constructor($class);

        $this->assertTrue($instance->isClassMethodCall());
        $this->assertFalse($instance->isCallable());
        $this->assertNotNull($instance->class);
        $this->assertSame('__construct', $instance->invokable);
        $this->assertEmpty($instance->arguments);
    }

    /**
     * - `with()` modifies the invocation in place and returns the same instance,
     *   allowing fluent method chaining.
     */
    #[Test, DataProvider('withArgumentsDataProvider')]
    public function withArgumentsMutatesAndReturnsTheSameInstance(string|object $class, string $method, array $arguments): void
    {
        $instance = Invocation::method($class, $method);
        $result   = $instance->with($arguments);

        $this->assertSame($instance, $result);
        $this->assertSame($arguments, $instance->arguments);
    }

    /**
     * - Calling `with()` multiple times accumulates arguments across calls rather
     *   than discarding previously provided values.
     */
    #[Test]
    public function withArgumentsMergesSubsequentCalls(): void
    {
        $instance = Invocation::method(ClassWithMethods::class, 'callableMethod');
        $instance->with(['arg1' => 'value1']);
        $instance->with(['arg2' => 'value2']);

        $this->assertSame(['arg1' => 'value1', 'arg2' => 'value2'], $instance->arguments);
    }
}
