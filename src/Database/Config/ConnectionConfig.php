<?php
declare(strict_types=1);

namespace Engine\Database\Config;

use Engine\Config\BaseConfigObject;

/**
 * Connection Config
 * -----------------
 *
 * Represents the configuration for a database connection.
 *
 * @phpstan-type ConnectionConfigArray array{
 *     host: string|null,
 *     port: int|null,
 *     socket: string|null,
 *     database: string,
 *     username: string,
 *     password: string,
 *     options: array<mixed>,
 * }
 *
 * @extends BaseConfigObject<ConnectionConfigArray>
 *
 * @phpstan-pure
 *
 * @immutable
 */
final readonly class ConnectionConfig extends BaseConfigObject
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
    protected function __construct(
        public ?string $host,
        public ?int    $port,
        public ?string $socket,
        public string  $database,
        public string  $username,
        public string  $password,
        public array   $options = [],
    ) {
        $this->driver = 'mysql';
    }
}
