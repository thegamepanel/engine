<?php
declare(strict_types=1);

namespace Engine\Values\Exceptions;

use InvalidArgumentException;

final class InvalidValueCastException extends InvalidArgumentException
{
    public static function cannotCast(string $name, string $type): self
    {
        return new self(sprintf(
            'Value "%s" is not %s and cannot be cast to one.',
            $name,
            $type,
        ));
    }
}
