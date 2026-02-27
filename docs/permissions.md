# Permissions

Permissions follow a structured string format:

```
module:feature[.subfeature].action
```

For example:

- `servers:create`
- `servers:console.read`
- `servers:console.write`
- `files:read`
- `files:write`
- `backups:create`
- `backups:delete`

The **module** segment maps to the module that registered the permission. Each module registers its own namespace at
boot, so third-party modules get their own namespace automatically with no risk of collision.

The **feature** segment identifies the specific capability within that module. An optional **subfeature** segment can be
used to further group related actions - the dot is a naming convention, not a separate tier of resolution.

The **action** segment describes what is being permitted, such as `read`, `write`, `create`, `delete`, or any other
action the module defines.

## Wildcards

Wildcards are supported at any level:

- `servers:console.*` - all actions on the console feature
- `servers:*` - all permissions within the servers module
- `*:*` - all permissions across all modules (superadmin)

A wildcard expands everything after the colon, including dotted subpermissions. Resolution is straightforward: split on
`:`, match the namespace, then either match the permission string exactly or check for `*`.

## Registration

Modules register their permissions through a collector. The namespace is automatically scoped to the module, so only the
feature and action need to be provided:

```php
$collector->register('create', 'delete', 'console.read', 'console.write');
```

The full permission strings (`servers:create`, `servers:console.read`, etc.) are resolved automatically using the
module's namespace. This means there is no risk of a module accidentally registering permissions under another module's
namespace.

Each permission string is parsed into a `Permission` value object that understands how to match against other
permissions, including wildcard expansion. A role holds a collection of these objects, and a permission check is simply
asking whether the collection allows a given permission.

## Effective Permissions

A user's effective permissions are the union of all permissions across every role assigned to them in the relevant
context. There are no deny rules - permissions are purely additive.
