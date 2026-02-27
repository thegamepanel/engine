<?php
declare(strict_types=1);

namespace Engine\Database;

final readonly class Row
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

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->data;
    }
}
