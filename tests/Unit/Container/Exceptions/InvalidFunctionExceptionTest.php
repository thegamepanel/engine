<?php
declare(strict_types=1);

namespace Tests\Unit\Container\Exceptions;

use Engine\Container\Contracts\ContainerException;
use Engine\Container\Exceptions\InvalidFunctionException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class InvalidFunctionExceptionTest extends TestCase
{
    #[Test]
    public function it_implements_container_exception(): void
    {
        $exception = InvalidFunctionException::make('bad_function');

        $this->assertInstanceOf(ContainerException::class, $exception);
    }

    #[Test]
    public function it_extends_runtime_exception(): void
    {
        $exception = InvalidFunctionException::make('bad_function');

        $this->assertInstanceOf(RuntimeException::class, $exception);
    }

    #[Test]
    public function make_includes_function_and_preserves_previous(): void
    {
        $previous  = new RuntimeException('original');
        $exception = InvalidFunctionException::make('some_function', $previous);

        $this->assertStringContainsString('some_function', $exception->getMessage());
        $this->assertSame($previous, $exception->getPrevious());
    }
}
