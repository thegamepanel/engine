<?php
declare(strict_types=1);

namespace Engine\Database\Exceptions;

use LogicException;

/**
 * Invalid Schema Exception
 * ------------------------
 *
 * Thrown when a schema definition contains a logic error, such as calling a
 * modifier on an incompatible type or generating SQL from an incomplete
 * definition.
 */
final class InvalidSchemaException extends LogicException
{
    /**
     * The modifier is not compatible with the given type.
     *
     * @param string $modifier
     * @param string $type
     *
     * @return self
     */
    public static function incompatibleModifier(string $modifier, string $type): self
    {
        return new self(
            sprintf('The modifier "%s" is not compatible with the type "%s"', $modifier, $type),
        );
    }

    /**
     * The modifier can only be used on a generated column.
     *
     * @param string $modifier
     *
     * @return self
     */
    public static function requiresGeneratedColumn(string $modifier): self
    {
        return new self(
            sprintf('The modifier "%s" can only be used on a generated column', $modifier),
        );
    }

    /**
     * The foreign key is missing a required clause.
     *
     * @param string $missing
     *
     * @return self
     */
    public static function incompleteForeignKey(string $missing): self
    {
        return new self(
            sprintf('Foreign key is missing a "%s" clause', $missing),
        );
    }
}
