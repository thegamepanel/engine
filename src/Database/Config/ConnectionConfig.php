<?php
declare(strict_types=1);

namespace Engine\Database\Config;

use Engine\Config\Contracts\ConfigObject;
use Webmozart\Assert\Assert;

/**
 * Connection Config
 * -----------------
 *
 * Represents the configuration for a database connection.
 *
 * @phpstan-pure
 *
 * @immutable
 */
final readonly class ConnectionConfig implements ConfigObject
{
    /**
     * @param string|null  $host
     * @param int|null     $port
     * @param string|null  $socket
     * @param string       $database
     * @param string       $username
     * @param string       $password
     * @param array<mixed> $options
     *
     * @return ConnectionConfig
     */
    public static function make(
        ?string $host,
        ?int    $port,
        ?string $socket,
        string  $database,
        string  $username,
        string  $password,
        array   $options = [],
    ): self {
        return new self($host, $port, $socket, $database, $username, $password, $options);
    }

    /**
     * Create a new config object from an array.
     *
     * Creates a new instance of the config object using the provided data
     * pulled from the config storage.
     *
     * @param array<array-key, mixed> $data
     *
     * @return static
     */
    public static function fromArray(array $data): static
    {
        Assert::keyExists($data, 'database', 'Database name is not defined.');
        Assert::keyExists($data, 'username', 'Username is not defined.');
        Assert::keyExists($data, 'password', 'Password is not defined.');

        if (isset($data['options'])) {
            Assert::isArray($data['options'], 'Options is not an array.');
        }

        if (isset($data['socket'])) {
            /**
             * @var array{
             *  socket: string,
             *  database: string,
             *  username: string,
             *  password: string,
             *  options?: array<int|string, mixed>
             * } $data
             */

            return new self(
                host: null,
                port: null,
                socket: $data['socket'],
                database: $data['database'],
                username: $data['username'],
                password: $data['password'],
                options: $data['options'] ?? [],
            );
        }

        Assert::keyExists($data, 'host', 'Host is not defined.');
        Assert::keyExists($data, 'port', 'Port is not defined.');
        Assert::integer($data['port'], 'Port is not an integer.');

        /**
         * @var array{
         *  host: string,
         *  port: int,
         *  database: string,
         *  username: string,
         *  password: string,
         *  options?: array<int|string, mixed>
         * } $data
         */
        return new self(
            host: $data['host'],
            port: $data['port'],
            socket: null,
            database: $data['database'],
            username: $data['username'],
            password: $data['password'],
            options: $data['options'] ?? [],
        );
    }

    public string $driver;

    /**
     * @param string|null  $host
     * @param int|null     $port
     * @param string|null  $socket
     * @param string       $database
     * @param string       $username
     * @param string       $password
     * @param array<mixed> $options
     */
    private function __construct(
        public ?string $host,
        public ?int    $port,
        public ?string $socket,
        public string  $database,
        public string  $username,
        public string  $password,
        public array   $options = [],
    ) {
        Assert::stringNotEmpty($database, 'Database name is not defined.');
        Assert::stringNotEmpty($username, 'Username is not defined.');
        Assert::stringNotEmpty($password, 'Password is not defined.');

        if ($socket !== null) {
            Assert::stringNotEmpty($socket, 'Socket path is not defined.');
        } else {
            Assert::stringNotEmpty($host, 'Host is not defined.');
            Assert::notNull($port, 'Port is not defined.');
        }

        $this->driver = 'mysql';
    }
}
