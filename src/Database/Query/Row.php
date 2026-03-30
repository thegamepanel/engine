<?php
declare(strict_types=1);

namespace Engine\Database\Query;

use Engine\Values\Contracts\GetsAsType;
use Engine\Values\ValueGetter;

final readonly class Row implements GetsAsType
{
    /**
     * @var array<string, mixed>
     */
    private array $data;

    /**
     * @param array<string, mixed> $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function get(string $column): mixed
    {
        return $this->data[$column] ?? null;
    }

    public function has(string $column): bool
    {
        return array_key_exists($column, $this->data);
    }

    public function isNull(string $column): bool
    {
        if ($this->has($column) === false) {
            return false;
        }

        return $this->get($column) === null;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->data;
    }

    /**
     * Get a value as a string.
     *
     * @param string $name
     *
     * @return string
     */
    public function string(string $name): string
    {
        return ValueGetter::string($name, $this->data);
    }

    /**
     * Get a value as an integer.
     *
     * @param string $name
     *
     * @return int
     */
    public function int(string $name): int
    {
        return ValueGetter::int($name, $this->data);
    }

    /**
     * Get a value as a float.
     *
     * @param string $name
     *
     * @return float
     */
    public function float(string $name): float
    {
        return ValueGetter::float($name, $this->data);
    }

    /**
     * Get a value as a boolean.
     *
     * @param string $name
     *
     * @return bool
     */
    public function bool(string $name): bool
    {
        return ValueGetter::bool($name, $this->data);
    }

    /**
     * Get a value as an array.
     *
     * @param string $name
     *
     * @return array<mixed>
     *
     * @noinspection PhpDocMissingThrowsInspection
     */
    public function array(string $name): array
    {
        return ValueGetter::array($name, $this->data);
    }
}
