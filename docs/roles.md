# Roles

Roles are defined and managed exclusively within the platform context. Each role carries a `context` that determines
where it can be assigned:

- **`platform`** - governs what a user can do within the platform context, such as managing infrastructure or assigning
  roles to others. Assigned directly to users at the platform level.
- **`server`** - governs what a user can do within a specific server's context. Assigned to users on a per-server basis.

A role is a named collection of permissions. The permissions available to assign to a role depend on the modules that
are installed and registered - each module registers its own permission definitions at boot time.

## Assignment

The ability to assign a role to another user is itself a permission. A user may only grant roles they have explicit
permission to assign, preventing privilege escalation. You cannot grant more than you have.

Server roles are assigned through a three-way relationship between a server, a user, and a role. A user may hold
multiple roles on a single server, and their effective permissions are the union of all permissions across those roles.

## Permissions

See [Permissions](./permissions.md) for how permissions are structured and resolved.
