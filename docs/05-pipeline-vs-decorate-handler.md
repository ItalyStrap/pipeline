# Pipeline vs DecorateHandler

Both `Pipeline` and `DecorateHandler` compose middleware with handlers, but they serve different use cases.

## Comparison

| | Pipeline | DecorateHandler |
|---|---|---|
| **Middleware addition** | Via constructor (variadic) | Fixed at construction |
| **Reusable** | No — single dispatch, then throws | Yes — can call `handle()` repeatedly |
| **Middleware count** | Any number | Exactly one per layer (nest for more) |
| **Mutable** | Yes — array_shift consumes the stack | No — immutable |
| **Use case** | Build a pipeline, dispatch once | Compose a fixed, reusable handler decorator |

## When to Use Pipeline

Use `Pipeline` when you need to **build a pipeline** — for example, when middleware is resolved from a container or assembled conditionally:

```php
$middleware = [new ValidationMiddleware()];

if ($config->loggingEnabled()) {
    $middleware[] = new LoggingMiddleware();
}

$handler = new Pipeline(
    new CreateUserHandler(),
    ...$middleware,
);

$result = $handler->handle($command);
```

Think of it as a **disposable pipeline**: assemble, dispatch, discard.

## When to Use DecorateHandler

Use `DecorateHandler` when you have a **fixed, known pipeline** that should be reusable across multiple messages:

```php
$handler = new DecorateHandler(
    new LoggingMiddleware(),
    new DecorateHandler(
        new ValidationMiddleware(),
        new CreateUserHandler(),
    ),
);

// Reuse for multiple messages
$handler->handle($messageA);
$handler->handle($messageB);
$handler->handle($messageC);
```

Think of it as a **composed handler**: each layer is a permanent decoration around the next.

## Can They Work Together?

Yes. For example, use `DecorateHandler` to build reusable inner layers and `Pipeline` to add dynamic outer middleware:

```php
$inner = new DecorateHandler(
    new ValidationMiddleware(),
    new CreateUserHandler(),
);

$handler = new Pipeline(
    $inner,
    new RequestScopedLoggingMiddleware($requestId),
);

$result = $handler->handle($message);
```

## Summary

- **Need runtime flexibility?** → `Pipeline`
- **Need reusability?** → `DecorateHandler`
- **Need both?** → Use `DecorateHandler` as the handler inside a `Pipeline`
