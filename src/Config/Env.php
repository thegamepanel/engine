<?php
declare(strict_types=1);

namespace Engine\Config;

use Dotenv\Dotenv;
use Engine\Config\Exceptions\InvalidEnvException;

/**
 * Environment Config
 * ------------------
 *
 * Acts as a container for environment variables, whether from an env file, or
 * the <code>$_ENV</code> superglobal.
 */
final class Env
{
    /**
     * The singleton instance.
     *
     * @var Env|null
     */
    private static ?self $instance = null;

    /**
     * Create an instance from the superglobal.
     */
    public static function createFromSuperglobal(): void
    {
        /** @var array<string, string|int|float|bool|null> $values */
        $values = $_ENV;

        self::$instance = new self($values);
    }

    /**
     * Create an instance from an env file.
     *
     * @param string $path
     */
    public static function createFromFile(string $path): void
    {
        $dotenv = Dotenv::createArrayBacked($path);

        /** @var array<string, string|int|float|bool|null> $values */
        $values = $dotenv->load();

        self::$instance = new self($values);
    }

    /**
     * Get an environment variable by key.
     *
     * Retrieves an environment variable by its key. If the variable is missing,
     * or <code>null</code>, then <code>$default</code> is returned.
     *
     * @param string                     $key
     * @param string|int|float|bool|null $default
     *
     * @return string|int|float|bool|null
     */
    public static function get(string $key, bool|float|int|string|null $default = null): bool|float|int|string|null
    {
        return self::$instance->values[$key] ?? $default;
    }

    /**
     * Check if an environment variable is present.
     *
     * Checks for the present of an environment variable by key. It will return
     * <code>true</code> if the key is present, even if the value associated
     * with it is <code>null</code>.
     *
     * @param string $key
     *
     * @return bool
     */
    public static function has(string $key): bool
    {
        return array_key_exists($key, self::$instance->values ?? []);
    }

    /**
     * Get an environment variable as a string.
     *
     * Wraps {@see self::get()} but ensures that the return value is a
     * <code>string</code>. If the value is a <code>string</code>,
     * <code>bool</code> or numeric, it is cast and returned. If it's missing,
     * <code>null</code>, or of an invalid type, <code>$default</code> is
     * returned instead.
     *
     * @template TDefault of string|null
     *
     * @param string   $key
     * @param TDefault $default
     *
     * @return (TDefault is string ? string : null)
     */
    public static function string(string $key, ?string $default = null): ?string
    {
        $value = self::get($key, $default);

        if (is_string($value) || is_numeric($value) || is_bool($value)) {
            return (string) $value;
        }

        return $default;
    }

    /**
     * Get an environment variable as an int.
     *
     * Wraps {@see self::get()} but ensures that the return value is an
     * <code>int</code>. If the value is an <code>int</code>,
     * <code>bool</code> or numeric, it is cast and returned. If it's missing,
     * or <code>null</code>, <code>$default</code> is
     * returned instead, otherwise an exception is thrown.
     *
     * @template TDefault of int|null
     *
     * @param string   $key
     * @param TDefault $default
     *
     * @return (TDefault is int ? int : null)
     *
     * @throws InvalidEnvException
     */
    public static function int(string $key, ?int $default = null): ?int
    {
        $value = self::get($key, $default);

        if (is_int($value) || is_numeric($value) || is_bool($value)) {
            return (int) $value;
        }

        if ($value === null) {
            return $default;
        }

        throw InvalidEnvException::make($key, 'int');
    }

    /**
     * Get an environment variable as a float.
     *
     * Wraps {@see self::get()} but ensures that the return value is a
     * <code>float</code>. If the value is a <code>float</code>,
     * <code>bool</code> or numeric, it is cast and returned. If it's missing,
     * or <code>null</code>, <code>$default</code> is
     * returned instead, otherwise an exception is thrown.
     *
     * @template TDefault of float|null
     *
     * @param string   $key
     * @param TDefault $default
     *
     * @return (TDefault is float ? float : null)
     */
    public static function float(string $key, ?float $default = null): ?float
    {
        $value = self::get($key, $default);

        if (is_float($value) || is_numeric($value) || is_bool($value)) {
            return (float) $value;
        }

        if ($value === null) {
            return $default;
        }

        throw InvalidEnvException::make($key, 'float');
    }

    /**
     * Get an environment variable as a bool.
     *
     * Wraps {@see self::get()} but ensures that the return value is a
     * <code>bool</code>. If the value is an <code>int</code> or
     * <code>bool</code> it is cast and returned. If the value is a
     * <code>string</code> containing a truthy value, <code>true</code> is returned.
     * If it's a <code>string</code> containing a falsey value,
     * <code>false</code> is returned.
     * If it's missing, or <code>null</code>, <code>$default</code> is
     * returned instead, otherwise an exception is thrown.
     *
     * Truthy values are:
     *   - <code>'true'</code>
     *   - <code>'1'</code>
     *   - <code>'yes'</code>
     *
     * Falsey values are:
     *   - <code>'false'</code>
     *   - <code>'0'</code>
     *   - <code>'no'</code>
     *
     * @template TDefault of bool|null
     *
     * @param string   $key
     * @param TDefault $default
     *
     * @return (TDefault is bool ? bool : null)
     */
    public static function bool(string $key, ?bool $default = null): ?bool
    {
        $value = self::get($key, $default);

        if (is_bool($value) || is_int($value)) {
            return (bool) $value;
        }

        if (is_string($value)) {
            return match ($value) {
                'true', '1', 'yes' => true,
                'false', '0', 'no' => false,
                default => throw InvalidEnvException::make($key, 'bool'),
            };
        }

        if ($value === null) {
            return $default;
        }

        throw InvalidEnvException::make($key, 'bool');
    }

    /**
     * Destroy the singleton instance.
     *
     * Sets the instance to <code>null</code> which effectively removes the
     * env variables from memory, unless they were loaded using the
     * <code>$_ENV</code> superglobal.
     */
    public static function destroy(): void
    {
        self::$instance = null;
    }

    /**
     * @param array<string, string|int|float|bool|null> $values
     */
    private function __construct(
        private readonly array $values = [],
    ) {
    }
}
