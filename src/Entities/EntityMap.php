<?php
declare(strict_types=1);

namespace Engine\Entities;

use Engine\Entities\Contracts\Entity;

final class EntityMap
{
    /**
     * @var array<class-string<\Engine\Entities\Contracts\Entity<\Engine\Entities\EntityId>>, array<string, array<string, mixed>>>
     */
    private array $snapshots = [];

    /**
     * @var array<class-string<\Engine\Entities\Contracts\Entity<\Engine\Entities\EntityId>>, array<string, \Engine\Entities\Contracts\Entity<\Engine\Entities\EntityId>>>
     */
    private array $identities = [];

    /**
     * Add an entity to the map.
     *
     * @param \Engine\Entities\Contracts\Entity<\Engine\Entities\EntityId> $entity
     * @param array<string, mixed>                                         $data
     *
     * @return void
     */
    public function add(Entity $entity, array $data): void
    {
        // Add a snapshot of the data.
        $this->snapshots[$entity::class][$entity->getId()->id] = $data;

        // Add the identity.
        $this->identities[$entity::class][$entity->getId()->id] = $entity;
    }

    /**
     * Check if the entity exists in the map.
     *
     * @template TEntityId of \Engine\Entities\EntityId
     * @template TEntity of \Engine\Entities\Contracts\Entity<TEntityId>
     *
     * @param class-string<TEntity> $entity
     * @param string                $id
     *
     * @return bool
     *
     * @phpstan-assert-if-true \Engine\Entities\Contracts\Entity<\Engine\Entities\EntityId> $this->get()
     */
    public function has(string $entity, string $id): bool
    {
        return isset($this->identities[$entity][$id])
               || isset($this->snapshots[$entity][$id]);
    }

    /**
     * Get the entity from the map.
     *
     * @template TEntityId of \Engine\Entities\EntityId
     * @template TEntity of \Engine\Entities\Contracts\Entity<TEntityId>
     *
     * @param class-string<TEntity> $entity
     * @param string                $id
     *
     * @return TEntity|null
     */
    public function get(string $entity, string $id): ?Entity
    {
        /** @var TEntity|null */
        return $this->identities[$entity][$id] ?? null;
    }

    /**
     * Get the changes between the current data and the snapshot.
     *
     * @template TEntityId of \Engine\Entities\EntityId
     * @template TEntity of \Engine\Entities\Contracts\Entity<TEntityId>
     *
     * @param class-string<TEntity> $entity
     * @param string                $id
     * @param array<string, mixed>  $data
     *
     * @return array<string, mixed>
     */
    public function changes(string $entity, string $id, array $data): array
    {
        $snapshot = $this->snapshots[$entity][$id] ?? [];

        if (empty($snapshot)) {
            return $data;
        }

        $changes = array_diff_assoc($data, $snapshot);

        // Ensure that the ID never makes it to the changes, so that we don't
        // end up introducing the ability to change it.
        if (isset($changes['id'])) {
            unset($changes['id']);
        }

        return $changes;
    }

    /**
     * Forget the entity from the map.
     *
     * @template TEntityId of \Engine\Entities\EntityId
     * @template TEntity of \Engine\Entities\Contracts\Entity<TEntityId>
     *
     * @param class-string<TEntity> $entity
     * @param string $id
     *
     * @return void
     */
    public function forget(string $entity, string $id): void
    {
        unset($this->identities[$entity][$id], $this->snapshots[$entity][$id]);
    }
}
