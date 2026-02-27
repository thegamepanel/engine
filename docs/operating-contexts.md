# Operating Contexts

The Game Panel operates across three distinct contexts, each with its own surface area, responsibilities, and access
model. Understanding the boundaries between them is important when building modules or extending the panel.

## Platform Context

The platform context is the administrative backbone of the panel. It is responsible for infrastructure-level concerns -
managing nodes, defining roles, creating servers, and overseeing the platform as a whole. Access to the platform context
is governed by platform-scoped roles assigned directly to users.

This is also where all roles are defined and managed, regardless of whether those roles apply at the platform level or
the server level.

## Server Context

The server context is the primary collaborative surface of the panel. Each server is an independent resource that users
can be invited to manage. Permissions within a server are governed by server-scoped [roles](./roles.md), assigned on a 
per-user,
per-server basis. A user may hold multiple roles on a single server.

Server access is managed through a three-way relationship between a server, a user, and a role. Grouping servers
together is supported as a convenience - inviting a user to a server group cascades into individual server invitations -
but permissions always resolve at the individual server level.

## Account Context

The account context is personal to the authenticated user. It covers user-specific concerns such as profile settings and
preferences. It has no collaborative surface and no role system of its own - it is simply the user operating on their
own data.
