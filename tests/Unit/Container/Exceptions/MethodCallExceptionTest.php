<?php
declare(strict_types=1);

namespace Tests\Unit\Container\Exceptions;

use Engine\Container\Contracts\ContainerException;
use Engine\Container\Exceptions\MethodCallException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class MethodCallExceptionTest extends TestCase
{
    #[Test]
    public function it_implements_container_exception(): void
    {
        $exception = MethodCallException::make('MyClass', 'myMethod');

        $this->assertInstanceOf(ContainerException::class, $exception);
    }

    #[Test]
    public function it_extends_runtime_exception(): void
    {
        $exception = MethodCallException::make('MyClass', 'myMethod');

        $this->assertInstanceOf(RuntimeException::class, $exception);
    }

    #[Test]
    public function make_includes_class_method_and_preserves_previous(): void
    {
        $previous  = new RuntimeException('original');
        $exception = MethodCallException::make('App\\Service', 'process', $previous);

        $this->assertStringContainsString('App\\Service', $exception->getMessage());
        $this->assertStringContainsString('process', $exception->getMessage());
        $this->assertSame($previous, $exception->getPrevious());
    }
}
