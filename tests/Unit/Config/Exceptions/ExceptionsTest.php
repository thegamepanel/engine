<?php
declare(strict_types=1);

namespace Tests\Unit\Config\Exceptions;

use Engine\Config\Contracts\ConfigException;
use Engine\Config\Exceptions\EnvInitialisationException;
use Engine\Config\Exceptions\InvalidEnvException;
use Engine\Config\Exceptions\MissingEnvVariableException;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('unit'), Group('config'), Group('exceptions')]
class ExceptionsTest extends TestCase
{
    // -------------------------------------------------------------------------
    // InvalidEnvException
    // -------------------------------------------------------------------------

    /**
     * - make() produces the expected message including variable name and type.
     */
    #[Test]
    public function invalidEnvExceptionMakeCreatesCorrectMessage(): void
    {
        $e = InvalidEnvException::make('MY_VAR', 'int');

        $this->assertInstanceOf(InvalidEnvException::class, $e);
        $this->assertSame(
            'The environment variable "MY_VAR" must be of type int, or castable to it.',
            $e->getMessage(),
        );
    }

    /**
     * - InvalidEnvException implements the ConfigException marker interface.
     */
    #[Test]
    public function invalidEnvExceptionImplementsConfigException(): void
    {
        $e = InvalidEnvException::make('VAR', 'float');

        $this->assertInstanceOf(ConfigException::class, $e);
    }

    // -------------------------------------------------------------------------
    // MissingEnvVariableException
    // -------------------------------------------------------------------------

    /**
     * - make() produces the expected message including the variable name.
     */
    #[Test]
    public function missingEnvVariableExceptionMakeCreatesCorrectMessage(): void
    {
        $e = MissingEnvVariableException::make('MY_VAR');

        $this->assertInstanceOf(MissingEnvVariableException::class, $e);
        $this->assertSame(
            'The environment variable "MY_VAR" is missing.',
            $e->getMessage(),
        );
    }

    /**
     * - MissingEnvVariableException implements the ConfigException marker interface.
     */
    #[Test]
    public function missingEnvVariableExceptionImplementsConfigException(): void
    {
        $e = MissingEnvVariableException::make('VAR');

        $this->assertInstanceOf(ConfigException::class, $e);
    }

    // -------------------------------------------------------------------------
    // EnvInitialisationException
    // -------------------------------------------------------------------------

    /**
     * - notInitialised() produces the expected message.
     */
    #[Test]
    public function envInitialisationNotInitialisedCreatesCorrectMessage(): void
    {
        $e = EnvInitialisationException::notInitialised();

        $this->assertInstanceOf(EnvInitialisationException::class, $e);
        $this->assertSame('The Env class has not been initialised.', $e->getMessage());
    }

    /**
     * - alreadyInitialised() produces the expected message.
     */
    #[Test]
    public function envInitialisationAlreadyInitialisedCreatesCorrectMessage(): void
    {
        $e = EnvInitialisationException::alreadyInitialised();

        $this->assertInstanceOf(EnvInitialisationException::class, $e);
        $this->assertSame('The Env class has already been initialised.', $e->getMessage());
    }

    /**
     * - EnvInitialisationException implements the ConfigException marker interface.
     */
    #[Test]
    public function envInitialisationExceptionImplementsConfigException(): void
    {
        $e = EnvInitialisationException::notInitialised();

        $this->assertInstanceOf(ConfigException::class, $e);
    }
}
