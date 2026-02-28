<?php
declare(strict_types=1);

namespace Engine\Core;

enum OperatingContext: string
{
    case Account = 'account';

    case Server = 'server';

    case Platform = 'platform';

    /**
     * @param array<string> $contexts
     *
     * @return list<\Engine\Core\OperatingContext>
     */
    public static function collect(array $contexts): array
    {
        $instances = [];

        foreach ($contexts as $context) {
            $instance = self::from($context);

            if (! in_array($instance, $instances, true)) {
                $instances[] = $instance;
            }
        }

        return $instances;
    }
}
