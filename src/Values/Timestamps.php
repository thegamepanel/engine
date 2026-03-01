<?php
declare(strict_types=1);

namespace Engine\Values;

use DateTimeImmutable;
use InvalidArgumentException;

final class Timestamps
{
    /**
     * @var array<string, \DateTimeImmutable|null>
     */
    private array $timestamps = [];

    public function __construct(?DateTimeImmutable ...$timestamps)
    {
        foreach ($timestamps as $name => $timestamp) {
            if (! is_string($name)) {
                throw new InvalidArgumentException('Timestamps must be named');
            }

            $this->add($name, $timestamp);
        }
    }

    public function get(string $name): ?DateTimeImmutable
    {
        return $this->timestamps[$name] ?? null;
    }

    public function has(string $name): bool
    {
        return array_key_exists($name, $this->timestamps);
    }

    public function add(string $name, ?DateTimeImmutable $timestamp): self
    {
        if ($this->has($name) === false) {
            return $this->set($name, $timestamp);
        }

        return $this;
    }

    public function set(string $name, ?DateTimeImmutable $timestamp): self
    {
        $this->timestamps[$name] = $timestamp;

        return $this;
    }
}
