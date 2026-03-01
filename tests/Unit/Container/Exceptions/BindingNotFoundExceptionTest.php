<?php
declare(strict_types=1);

namespace Tests\Unit\Container\Exceptions;

use Engine\Container\Contracts\ContainerException;
use Engine\Container\Exceptions\BindingNotFoundException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class BindingNotFoundExceptionTest extends TestCase
{
    #[Test]
    public function it_implements_container_exception(): void
    {
        $exception = BindingNotFoundException::class('SomeClass');

        $this->assertInstanceOf(ContainerException::class, $exception);
    }

    #[Test]
    public function it_extends_runtime_exception(): void
    {
        $exception = BindingNotFoundException::class('SomeClass');

        $this->assertInstanceOf(RuntimeException::class, $exception);
    }

    #[Test]
    public function class_includes_class_name_in_message(): void
    {
        $exception = BindingNotFoundException::class('App\\Services\\Mailer');

        $this->assertStringContainsString('App\\Services\\Mailer', $exception->getMessage());
    }

    #[Test]
    public function named_includes_class_and_name_in_message(): void
    {
        $exception = BindingNotFoundException::named('App\\Services\\Mailer', 'primary');

        $this->assertStringContainsString('App\\Services\\Mailer', $exception->getMessage());
        $this->assertStringContainsString('primary', $exception->getMessage());
    }

    #[Test]
    public function qualified_includes_class_and_qualifier_in_message(): void
    {
        $exception = BindingNotFoundException::qualified('App\\Services\\Mailer', 'App\\Qualifiers\\Primary');

        $this->assertStringContainsString('App\\Services\\Mailer', $exception->getMessage());
        $this->assertStringContainsString('App\\Qualifiers\\Primary', $exception->getMessage());
    }
}
