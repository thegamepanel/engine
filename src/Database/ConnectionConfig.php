<?php
declare(strict_types=1);

namespace Engine\Database;

final readonly class ConnectionConfig
{
    /**
     * @param string       $driver
     * @param string       $host
     * @param int          $port
     * @param string       $database
     * @param string       $username
     * @param string       $password
     * @param array<mixed> $options
     */
    public function __construct(
        public string  $driver,
        public ?string $host,
        public ?int    $port,
        public ?string $socket,
        public string  $database,
        public string  $username,
        public string  $password,
        public array   $options = [],
    )
    {
    }
}
