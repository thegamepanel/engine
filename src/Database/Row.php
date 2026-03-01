<?php
declare(strict_types=1);

namespace Engine\Database;

use Engine\Values\Concerns\GetValueAsType;

final readonly class Row
{
    use GetValueAsType;

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

    protected function getValue(string $name): mixed
    {
        return $this->get($name);
    }
}
