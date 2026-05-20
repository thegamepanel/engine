<?php
declare(strict_types=1);

namespace Tests\Unit\Config\Exceptions;

use Engine\Config\Contracts\ConfigException;
use Engine\Config\Exceptions\ConfigLifecycleException;
use Engine\Config\Exceptions\ConfigNotRegisteredException;
use Engine\Config\Exceptions\EnvInitialisationException;
use Engine\Config\Exceptions\InvalidConfigException;
use Engine\Config\Exceptions\InvalidEnvException;
use Engine\Config\Exceptions\MissingEnvVariableException;
use LogicException;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;

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

    // -------------------------------------------------------------------------
    // InvalidConfigException
    // -------------------------------------------------------------------------

    /**
     * - hydrationFailed() includes the file, section, and message in the output.
     */
    #[Test]
    public function invalidConfigHydrationFailedIncludesFileSectionAndMessage(): void
    {
        $e = InvalidConfigException::hydrationFailed(
            file: 'config.toml',
            section: 'database',
            message: 'Password is empty',
        );

        $this->assertInstanceOf(InvalidConfigException::class, $e);
        $this->assertSame(
            'Invalid config in config.toml at [database]: Password is empty',
            $e->getMessage(),
        );
    }

    /**
     * - hydrationFailed() includes the key in the location when supplied.
     */
    #[Test]
    public function invalidConfigHydrationFailedIncludesKeyWhenSupplied(): void
    {
        $e = InvalidConfigException::hydrationFailed(
            file: 'config.toml',
            section: 'database',
            message: 'must be string',
            key: 'primary',
        );

        $this->assertSame(
            'Invalid config in config.toml at [database.primary]: must be string',
            $e->getMessage(),
        );
    }

    /**
     * - hydrationFailed() preserves the previous exception.
     */
    #[Test]
    public function invalidConfigHydrationFailedPreservesPrevious(): void
    {
        $previous = new RuntimeException('underlying');
        $e        = InvalidConfigException::hydrationFailed(
            file: 'config.toml',
            section: 'database',
            message: 'underlying',
            previous: $previous,
        );

        $this->assertSame($previous, $e->getPrevious());
    }

    /**
     * - envVarMissing() includes the variable name and the dotted path.
     */
    #[Test]
    public function invalidConfigEnvVarMissingIncludesVariableAndPath(): void
    {
        $e = InvalidConfigException::envVarMissing('DB_PASS', 'database.connections.primary.password');

        $this->assertSame(
            'Required environment variable "DB_PASS" is not set at [database.connections.primary.password].',
            $e->getMessage(),
        );
    }

    /**
     * - reservedKey() includes the key and the source file.
     */
    #[Test]
    public function invalidConfigReservedKeyIncludesKeyAndFile(): void
    {
        $e = InvalidConfigException::reservedKey('modules', 'config.toml');

        $this->assertSame(
            'The top-level key "modules" is reserved by the loader and may not be declared in config.toml.',
            $e->getMessage(),
        );
    }

    /**
     * - tomlParseError() includes the file path and preserves the previous exception.
     */
    #[Test]
    public function invalidConfigTomlParseErrorIncludesFileAndPreserves(): void
    {
        $previous = new RuntimeException('syntax');
        $e        = InvalidConfigException::tomlParseError('config.toml', $previous);

        $this->assertSame('Failed to parse TOML file config.toml: syntax', $e->getMessage());
        $this->assertSame($previous, $e->getPrevious());
    }

    /**
     * - fileReadError() includes the file path and reason in the message.
     */
    #[Test]
    public function invalidConfigFileReadErrorIncludesFileAndReason(): void
    {
        $e = InvalidConfigException::fileReadError('config.toml', 'Unable to read file.');

        $this->assertSame('Failed to read TOML file config.toml: Unable to read file.', $e->getMessage());
        $this->assertNull($e->getPrevious());
    }

    /**
     * - InvalidConfigException implements the ConfigException marker interface.
     */
    #[Test]
    public function invalidConfigExceptionImplementsConfigException(): void
    {
        $e = InvalidConfigException::reservedKey('modules', 'config.toml');

        $this->assertInstanceOf(ConfigException::class, $e);
    }

    // -------------------------------------------------------------------------
    // ConfigNotRegisteredException
    // -------------------------------------------------------------------------

    /**
     * - forClass() includes the class name in the message.
     */
    #[Test]
    public function configNotRegisteredForClassIncludesClassName(): void
    {
        $e = ConfigNotRegisteredException::forClass('App\Some\Config');

        $this->assertInstanceOf(ConfigNotRegisteredException::class, $e);
        $this->assertSame(
            'No config has been registered for class "App\Some\Config".',
            $e->getMessage(),
        );
    }

    /**
     * - ConfigNotRegisteredException implements the ConfigException marker interface.
     */
    #[Test]
    public function configNotRegisteredExceptionImplementsConfigException(): void
    {
        $e = ConfigNotRegisteredException::forClass('App\Some\Config');

        $this->assertInstanceOf(ConfigException::class, $e);
    }

    // -------------------------------------------------------------------------
    // ConfigLifecycleException
    // -------------------------------------------------------------------------

    /**
     * - coreAlreadySealed() produces the expected message.
     */
    #[Test]
    public function configLifecycleCoreAlreadySealed(): void
    {
        $e = ConfigLifecycleException::coreAlreadySealed();

        $this->assertInstanceOf(ConfigLifecycleException::class, $e);
        $this->assertSame('The core config has already been sealed.', $e->getMessage());
    }

    /**
     * - alreadySealed() produces the expected message.
     */
    #[Test]
    public function configLifecycleAlreadySealed(): void
    {
        $e = ConfigLifecycleException::alreadySealed();

        $this->assertSame('The config registry has already been sealed.', $e->getMessage());
    }

    /**
     * - registerBeforeCoreSeal() produces the expected message.
     */
    #[Test]
    public function configLifecycleRegisterBeforeCoreSeal(): void
    {
        $e = ConfigLifecycleException::registerBeforeCoreSeal();

        $this->assertSame(
            'Module configs cannot be registered before sealCore() has been called.',
            $e->getMessage(),
        );
    }

    /**
     * - registerAfterSeal() produces the expected message.
     */
    #[Test]
    public function configLifecycleRegisterAfterSeal(): void
    {
        $e = ConfigLifecycleException::registerAfterSeal();

        $this->assertSame(
            'Module configs cannot be registered after the registry has been sealed.',
            $e->getMessage(),
        );
    }

    /**
     * - registerAsCore() produces the expected message and includes the module name.
     */
    #[Test]
    public function configLifecycleRegisterAsCoreIncludesModule(): void
    {
        $e = ConfigLifecycleException::registerAsCore('engine');

        $this->assertSame(
            'The module name "engine" is reserved for core configs and cannot be used by register().',
            $e->getMessage(),
        );
    }

    /**
     * - readBeforeCoreSeal() produces the expected message.
     */
    #[Test]
    public function configLifecycleReadBeforeCoreSeal(): void
    {
        $e = ConfigLifecycleException::readBeforeCoreSeal();

        $this->assertSame(
            'Configs cannot be read from the registry before sealCore() has been called.',
            $e->getMessage(),
        );
    }

    /**
     * - readAfterSeal() produces the expected message.
     */
    #[Test]
    public function configLifecycleReadAfterSeal(): void
    {
        $e = ConfigLifecycleException::readAfterSeal();

        $this->assertSame(
            'Configs cannot be read from the registry after seal() has been called. Use the returned ConfigCatalogue.',
            $e->getMessage(),
        );
    }

    /**
     * - ConfigLifecycleException extends LogicException.
     */
    #[Test]
    public function configLifecycleExtendsLogicException(): void
    {
        $e = ConfigLifecycleException::alreadySealed();

        $this->assertInstanceOf(LogicException::class, $e);
    }

    /**
     * - ConfigLifecycleException implements the ConfigException marker interface.
     */
    #[Test]
    public function configLifecycleImplementsConfigException(): void
    {
        $e = ConfigLifecycleException::alreadySealed();

        $this->assertInstanceOf(ConfigException::class, $e);
    }
}
