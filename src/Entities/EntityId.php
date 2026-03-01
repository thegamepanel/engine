<?php
declare(strict_types=1);

namespace Engine\Entities;

use Symfony\Component\Uid\Ulid;

/**
 * Base class for ULID-based identifiers.
 *
 * @phpstan-pure
 */
abstract readonly class EntityId
{
    public static function make(): static
    {
        return new static(Ulid::generate());
    }

    public string $id;

    final public function __construct(string $id)
    {
        assert(Ulid::isValid($id), 'Invalid ULID');

        $this->id = $id;
    }

    public function getId(): string
    {
        return $this->id;
    }
}
