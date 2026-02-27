# Collectors

Collectors are the mechanism through which modules register their capabilities with the engine - permissions, actions,
and other concerns yet to be defined. Each type of collector has a corresponding argument type, and the engine's
`CollectorHandler` is responsible for discovering and invoking the appropriate methods at boot time.

## How It Works

Within a module's registrar, any public non-static method can be marked with the `#[Collect]` attribute to designate it
as a collector method. The method must declare a single argument typed to the relevant collector.

```php
use Engine\Collectors\Attributes\Collect;
use Engine\Collectors\PermissionCollector;

class MyModuleRegistrar
{
    #[Collect]
    public function permissions(PermissionCollector $collector): void
    {
        $collector->register('create', 'delete', 'console.read', 'console.write');
    }
}
```

The `CollectorHandler` inspects the registrar, finds all `#[Collect]`-attributed methods, resolves the appropriate
collector from the argument type, and calls the method with it. The module namespace is implicit - you never need to
provide it.

## Operating Context

The `#[Collect]` attribute accepts an optional `OperatingContext` value to scope the collector method to a specific
context:

```php
#[Collect(OperatingContext::Server)]
public function serverPermissions(PermissionCollector $collector): void
{
    $collector->register('console.read', 'console.write');
}

#[Collect(OperatingContext::Platform)]
public function platformPermissions(PermissionCollector $collector): void
{
    $collector->register('create', 'delete');
}
```

Not all collectors support all contexts. Depending on the collector, an unsupported context value may be ignored, or the
method itself may be skipped entirely. Refer to the documentation for each collector type for specifics.

## Collector Types

| Collector             | Description                                            |
|-----------------------|--------------------------------------------------------|
| `PermissionCollector` | Registers permissions scoped to the module's namespace |

More collector types will be added as the engine evolves.
