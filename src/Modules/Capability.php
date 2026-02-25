<?php
declare(strict_types=1);

namespace Engine\Modules;

enum Capability: string
{
    /**
     * Full, unrestricted access to everything.
     */
    case Unrestricted = 'unrestricted';

    /**
     * Add or modify validation for other modules input, including core.
     */
    case CrossValidate = 'cross-validate';

    /**
     * Read and write to outside its scoped settings.
     */
    case CrossSettings = 'cross-settings';

    /**
     * Register routes outside its own namespace.
     */
    case CrossRoutes = 'cross-routes';

    /**
     * Read and write outside its allotted storage.
     */
    case CrossStorage = 'cross-storage';

    /**
     * Modify or create database tables that are not explicitly tied to itself.
     */
    case ExtendSchema = 'extend-schema';

    /**
     * Inject or modify UI outside its own sections.
     */
    case ModifyUi = 'modify-ui';

    /**
     * Register artisan/CLI commands.
     */
    case SystemCommands = 'system-commands';

    /**
     * Communicate directly with node daemons without going through the services.
     */
    case DaemonAccess = 'daemon-access';
}
