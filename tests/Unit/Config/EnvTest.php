<?php
declare(strict_types=1);

namespace Tests\Unit\Config;

use Engine\Config\Env;
use Engine\Config\Exceptions\EnvInitialisationException;
use Engine\Config\Exceptions\InvalidEnvException;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('unit'), Group('config'), Group('env')]
class EnvTest extends TestCase
{
    protected function tearDown(): void
    {
        Env::destroy();
    }

    // -------------------------------------------------------------------------
    // createFromSuperglobal()
    // -------------------------------------------------------------------------

    /**
     * - createFromSuperglobal() makes $_ENV values accessible via get().
     */
    #[Test]
    public function createFromSuperglobalLoadsValues(): void
    {
        $previous = $_ENV;
        $_ENV     = ['FOO' => 'bar'];
        Env::createFromSuperglobal();
        $_ENV = $previous;

        $this->assertSame('bar', Env::get('FOO'));
    }

    /**
     * - Calling createFromSuperglobal() when already initialised throws.
     */
    #[Test]
    public function createFromSuperglobalThrowsWhenAlreadyInitialised(): void
    {
        $this->initEnv(['FOO' => 'bar']);

        $this->expectException(EnvInitialisationException::class);
        Env::createFromSuperglobal();
    }

    // -------------------------------------------------------------------------
    // createFromFile()
    // -------------------------------------------------------------------------

    /**
     * - createFromFile() loads values from the .env fixture file.
     */
    #[Test]
    public function createFromFileLoadsValues(): void
    {
        Env::createFromFile(__DIR__ . '/Fixtures');

        $this->assertSame('hello', Env::get('TEST_KEY'));
    }

    /**
     * - Calling createFromFile() when already initialised throws.
     */
    #[Test]
    public function createFromFileThrowsWhenAlreadyInitialised(): void
    {
        $this->initEnv([]);

        $this->expectException(EnvInitialisationException::class);
        Env::createFromFile(__DIR__ . '/Fixtures');
    }

    // -------------------------------------------------------------------------
    // destroy()
    // -------------------------------------------------------------------------

    /**
     * - After destroy(), calling any getter throws EnvInitialisationException.
     */
    #[Test]
    public function destroyClearsInstance(): void
    {
        $this->initEnv(['FOO' => 'bar']);
        Env::destroy();

        $this->expectException(EnvInitialisationException::class);
        Env::get('FOO');
    }

    // -------------------------------------------------------------------------
    // instance() guard — notInitialised
    // -------------------------------------------------------------------------

    /**
     * - Calling get() before initialisation throws EnvInitialisationException.
     */
    #[Test]
    public function getterThrowsWhenNotInitialised(): void
    {
        $this->expectException(EnvInitialisationException::class);
        Env::get('X');
    }

    // -------------------------------------------------------------------------
    // has()
    // -------------------------------------------------------------------------

    /**
     * - has() returns true for a key that is present.
     */
    #[Test]
    public function hasReturnsTrueForExistingKey(): void
    {
        $this->initEnv(['FOO' => 'bar']);

        $this->assertTrue(Env::has('FOO'));
    }

    /**
     * - has() returns false for a key that is absent.
     */
    #[Test]
    public function hasReturnsFalseForMissingKey(): void
    {
        $this->initEnv([]);

        $this->assertFalse(Env::has('MISSING'));
    }

    /**
     * - has() returns true even when the key's value is null.
     *   (Distinguishes array_key_exists from isset.)
     */
    #[Test]
    public function hasReturnsTrueForKeyWithNullValue(): void
    {
        $this->initEnv(['FOO' => null]);

        $this->assertTrue(Env::has('FOO'));
    }

    // -------------------------------------------------------------------------
    // get()
    // -------------------------------------------------------------------------

    /**
     * - get() returns the stored value for an existing key.
     */
    #[Test]
    public function getReturnsValueForExistingKey(): void
    {
        $this->initEnv(['FOO' => 'bar']);

        $this->assertSame('bar', Env::get('FOO'));
    }

    /**
     * - get() returns null when the key is absent and no default is given.
     */
    #[Test]
    public function getReturnsNullDefaultForMissingKey(): void
    {
        $this->initEnv([]);

        $this->assertNull(Env::get('MISSING'));
    }

    /**
     * - get() returns the explicit default when the key is absent.
     */
    #[Test]
    public function getReturnsExplicitDefaultForMissingKey(): void
    {
        $this->initEnv([]);

        $this->assertSame('fallback', Env::get('MISSING', 'fallback'));
    }

    // -------------------------------------------------------------------------
    // string()
    // -------------------------------------------------------------------------

    /**
     * - string() returns the value unchanged when it is already a string.
     */
    #[Test]
    public function stringReturnsStringValue(): void
    {
        $this->initEnv(['KEY' => 'hello']);

        $this->assertSame('hello', Env::string('KEY'));
    }

    /**
     * - string() casts an integer value to a string.
     */
    #[Test]
    public function stringCastsIntToString(): void
    {
        $this->initEnv(['KEY' => 42]);

        $this->assertSame('42', Env::string('KEY'));
    }

    /**
     * - string() casts a float value to a string.
     */
    #[Test]
    public function stringCastsFloatToString(): void
    {
        $this->initEnv(['KEY' => 3.14]);

        $this->assertSame('3.14', Env::string('KEY'));
    }

    /**
     * - string() casts bool true to '1'.
     */
    #[Test]
    public function stringCastsBoolTrueToString(): void
    {
        $this->initEnv(['KEY' => true]);

        $this->assertSame('1', Env::string('KEY'));
    }

    /**
     * - string() casts bool false to ''.
     */
    #[Test]
    public function stringCastsBoolFalseToString(): void
    {
        $this->initEnv(['KEY' => false]);

        $this->assertSame('', Env::string('KEY'));
    }

    /**
     * - string() returns null when the key is absent and no default is given.
     */
    #[Test]
    public function stringReturnsNullDefaultForMissingKey(): void
    {
        $this->initEnv([]);

        $this->assertNull(Env::string('MISSING'));
    }

    /**
     * - string() returns the explicit default when the key is absent.
     */
    #[Test]
    public function stringReturnsExplicitDefaultForMissingKey(): void
    {
        $this->initEnv([]);

        $this->assertSame('fb', Env::string('MISSING', 'fb'));
    }

    // -------------------------------------------------------------------------
    // int()
    // -------------------------------------------------------------------------

    /**
     * - int() returns the value unchanged when it is already an int.
     */
    #[Test]
    public function intReturnsIntValue(): void
    {
        $this->initEnv(['KEY' => 5]);

        $this->assertSame(5, Env::int('KEY'));
    }

    /**
     * - int() casts a numeric string to an int.
     */
    #[Test]
    public function intCastsNumericStringToInt(): void
    {
        $this->initEnv(['KEY' => '42']);

        $this->assertSame(42, Env::int('KEY'));
    }

    /**
     * - int() casts bool true to 1.
     */
    #[Test]
    public function intCastsBoolTrueToInt(): void
    {
        $this->initEnv(['KEY' => true]);

        $this->assertSame(1, Env::int('KEY'));
    }

    /**
     * - int() casts bool false to 0.
     */
    #[Test]
    public function intCastsBoolFalseToInt(): void
    {
        $this->initEnv(['KEY' => false]);

        $this->assertSame(0, Env::int('KEY'));
    }

    /**
     * - int() returns null when the key is absent and no default is given.
     */
    #[Test]
    public function intReturnsNullDefaultForMissingKey(): void
    {
        $this->initEnv([]);

        $this->assertNull(Env::int('MISSING'));
    }

    /**
     * - int() returns the explicit default when the key is absent.
     */
    #[Test]
    public function intReturnsExplicitDefaultForMissingKey(): void
    {
        $this->initEnv([]);

        $this->assertSame(7, Env::int('MISSING', 7));
    }

    /**
     * - int() throws InvalidEnvException for a non-castable string value.
     */
    #[Test]
    public function intThrowsInvalidEnvExceptionForNonCastable(): void
    {
        $this->initEnv(['KEY' => 'hello']);

        $this->expectException(InvalidEnvException::class);
        Env::int('KEY');
    }

    // -------------------------------------------------------------------------
    // float()
    // -------------------------------------------------------------------------

    /**
     * - float() returns the value unchanged when it is already a float.
     */
    #[Test]
    public function floatReturnsFloatValue(): void
    {
        $this->initEnv(['KEY' => 2.5]);

        $this->assertSame(2.5, Env::float('KEY'));
    }

    /**
     * - float() casts a numeric string to a float.
     */
    #[Test]
    public function floatCastsNumericStringToFloat(): void
    {
        $this->initEnv(['KEY' => '3.14']);

        $this->assertSame(3.14, Env::float('KEY'));
    }

    /**
     * - float() casts bool true to 1.0.
     */
    #[Test]
    public function floatCastsBoolTrueToFloat(): void
    {
        $this->initEnv(['KEY' => true]);

        $this->assertSame(1.0, Env::float('KEY'));
    }

    /**
     * - float() casts bool false to 0.0.
     */
    #[Test]
    public function floatCastsBoolFalseToFloat(): void
    {
        $this->initEnv(['KEY' => false]);

        $this->assertSame(0.0, Env::float('KEY'));
    }

    /**
     * - float() returns null when the key is absent and no default is given.
     */
    #[Test]
    public function floatReturnsNullDefaultForMissingKey(): void
    {
        $this->initEnv([]);

        $this->assertNull(Env::float('MISSING'));
    }

    /**
     * - float() returns the explicit default when the key is absent.
     */
    #[Test]
    public function floatReturnsExplicitDefaultForMissingKey(): void
    {
        $this->initEnv([]);

        $this->assertSame(1.5, Env::float('MISSING', 1.5));
    }

    /**
     * - float() throws InvalidEnvException for a non-castable string value.
     */
    #[Test]
    public function floatThrowsInvalidEnvExceptionForNonCastable(): void
    {
        $this->initEnv(['KEY' => 'hello']);

        $this->expectException(InvalidEnvException::class);
        Env::float('KEY');
    }

    // -------------------------------------------------------------------------
    // bool()
    // -------------------------------------------------------------------------

    /**
     * - bool() returns true when the stored value is bool true.
     */
    #[Test]
    public function boolReturnsBoolTrueValue(): void
    {
        $this->initEnv(['KEY' => true]);

        $this->assertTrue(Env::bool('KEY'));
    }

    /**
     * - bool() returns false when the stored value is bool false.
     */
    #[Test]
    public function boolReturnsBoolFalseValue(): void
    {
        $this->initEnv(['KEY' => false]);

        $this->assertFalse(Env::bool('KEY'));
    }

    /**
     * - bool() casts a non-zero int to true.
     */
    #[Test]
    public function boolCastsNonZeroIntToTrue(): void
    {
        $this->initEnv(['KEY' => 1]);

        $this->assertTrue(Env::bool('KEY'));
    }

    /**
     * - bool() casts zero int to false.
     */
    #[Test]
    public function boolCastsZeroIntToFalse(): void
    {
        $this->initEnv(['KEY' => 0]);

        $this->assertFalse(Env::bool('KEY'));
    }

    /**
     * - bool() converts the string 'true' to true.
     */
    #[Test]
    public function boolStringTrueReturnsTrue(): void
    {
        $this->initEnv(['KEY' => 'true']);

        $this->assertTrue(Env::bool('KEY'));
    }

    /**
     * - bool() converts the string '1' to true.
     */
    #[Test]
    public function boolString1ReturnsTrue(): void
    {
        $this->initEnv(['KEY' => '1']);

        $this->assertTrue(Env::bool('KEY'));
    }

    /**
     * - bool() converts the string 'yes' to true.
     */
    #[Test]
    public function boolStringYesReturnsTrue(): void
    {
        $this->initEnv(['KEY' => 'yes']);

        $this->assertTrue(Env::bool('KEY'));
    }

    /**
     * - bool() converts the string 'false' to false.
     */
    #[Test]
    public function boolStringFalseReturnsFalse(): void
    {
        $this->initEnv(['KEY' => 'false']);

        $this->assertFalse(Env::bool('KEY'));
    }

    /**
     * - bool() converts the string '0' to false.
     */
    #[Test]
    public function boolString0ReturnsFalse(): void
    {
        $this->initEnv(['KEY' => '0']);

        $this->assertFalse(Env::bool('KEY'));
    }

    /**
     * - bool() converts the string 'no' to false.
     */
    #[Test]
    public function boolStringNoReturnsFalse(): void
    {
        $this->initEnv(['KEY' => 'no']);

        $this->assertFalse(Env::bool('KEY'));
    }

    /**
     * - bool() throws InvalidEnvException for an unrecognised string value.
     */
    #[Test]
    public function boolThrowsForUnrecognisedString(): void
    {
        $this->initEnv(['KEY' => 'maybe']);

        $this->expectException(InvalidEnvException::class);
        Env::bool('KEY');
    }

    /**
     * - bool() returns null when the key is absent and no default is given.
     */
    #[Test]
    public function boolReturnsNullDefaultForMissingKey(): void
    {
        $this->initEnv([]);

        $this->assertNull(Env::bool('MISSING'));
    }

    /**
     * - bool() throws InvalidEnvException when the value is a float.
     *   (Covers the final throw branch — float is not bool/int/string/null.)
     */
    #[Test]
    public function boolThrowsForFloatValue(): void
    {
        $this->initEnv(['KEY' => 1.5]);

        $this->expectException(InvalidEnvException::class);
        Env::bool('KEY');
    }

    /**
     * Initialise Env from a plain array by briefly injecting values into $_ENV.
     *
     * @param array<string, mixed> $values
     */
    private function initEnv(array $values): void
    {
        $previous = $_ENV;
        $_ENV     = $values;
        Env::createFromSuperglobal();
        $_ENV = $previous;
    }
}
