<?php
declare(strict_types=1);

namespace Engine\Values\Concerns;

use InvalidArgumentException;

/**
 *
 */
trait GetValueAsType
{
    /**
     * Get a value.
     *
     * @param string $name
     *
     * @return mixed
     */
    abstract protected function getValue(string $name): mixed;

    /**
     * Get a value as a string.
     *
     * @param string $name
     *
     * @return string
     */
    public function string(string $name): string
    {
        $value = $this->getValue($name);

        if (is_string($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (string)$value;
        }

        throw new InvalidArgumentException(sprintf(
            'Value "%s" is not a string and cannot be cast to one.',
            $name
        ));
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
        $value = $this->getValue($name);

        if (is_int($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (int)$value;
        }

        throw new InvalidArgumentException(sprintf(
            'Value "%s" is not an integer and cannot be cast to one.',
            $name
        ));
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
        $value = $this->getValue($name);

        if (is_float($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (float)$value;
        }

        throw new InvalidArgumentException(sprintf(
            'Value "%s" is not a float and cannot be cast to one.',
            $name
        ));
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
        $value = $this->getValue($name);

        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value)) {
            return (bool)$value;
        }

        if (is_string($value)) {
            return match ($value) {
                'true', '1', 'yes' => true,
                'false', '0', 'no' => false,
                default            => throw new InvalidArgumentException(sprintf(
                    'Value "%s" is not a boolean and cannot be cast to one.',
                    $name
                ))
            };
        }

        throw new InvalidArgumentException(sprintf(
            'Value "%s" is not a bool and cannot be cast to one.',
            $name
        ));
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
        $value = $this->getValue($name);

        if (is_array($value)) {
            return $value;
        }

        if (is_string($value) && json_validate($value)) {
            return json_decode($value, true, 512, JSON_THROW_ON_ERROR);
        }

        throw new InvalidArgumentException(sprintf(
            'Value "%s" is not an array and cannot be cast to one.',
            $name
        ));
    }
}
