<?php

namespace Engine\Entities\Contracts;

use Engine\Entities\EntityId;

/**
 * @template TEntityId of \Engine\Entities\EntityId
 * @template TEntity of \Engine\Entities\Contracts\Entity<TEntityId>
 */
interface EntityStore
{
    /**
     * Find an entity by its ID.
     *
     * @param TEntityId $id
     *
     * @return TEntity|null
     */
    public function find(EntityId $id): ?Entity;

    /**
     * Save an entity.
     *
     * @param TEntity $entity
     *
     * @return bool
     */
    public function save(Entity $entity): bool;

    /**
     * Delete an entity.
     *
     * @param TEntity $entity
     *
     * @return bool
     */
    public function delete(Entity $entity): bool;
}
