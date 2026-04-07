# Helper Classes

## CallbackHandler

Wraps any `callable` as a `HandlerInterface`. Useful for inline handlers without creating a dedicated class:

```php
use ItalyStrap\Pipeline\CallbackHandler;

$handler = new CallbackHandler(static function (object $message): string {
    return 'Handled: ' . get_class($message);
});

$result = $handler->handle(new \stdClass()); // "Handled: stdClass"
```

Works with closures, first-class callable syntax, invokable objects — anything PHP considers `callable`.

---

## DecorateHandler

An immutable decorator that composes **one middleware** with **one handler** into a single `HandlerInterface`:

```php
use ItalyStrap\Pipeline\DecorateHandler;

$decorated = new DecorateHandler(
    new LoggingMiddleware(),
    new CreateUserHandler(),
);

$result = $decorated->handle($command);
// LoggingMiddleware runs, then delegates to CreateUserHandler
```

### Nesting

You can nest `DecorateHandler` instances to build a fixed pipeline:

```php
$pipeline = new DecorateHandler(
    new LoggingMiddleware(),
    new DecorateHandler(
        new ValidationMiddleware(),
        new CreateUserHandler(),
    ),
);

// LoggingMiddleware → ValidationMiddleware → CreateUserHandler
```

### Key Properties

- **Immutable** — the middleware and handler are fixed at construction.
- **Reusable** — can call `handle()` multiple times safely (no internal state is mutated).
- **No dynamic addition** — you cannot add middleware after construction.

---

## NoHandlerProvided

A handler that always throws. Used as the default in `Pipeline` when no handler is explicitly provided:

```php
use ItalyStrap\Pipeline\NoHandlerProvided;

$handler = new NoHandlerProvided();
$handler->handle(new \stdClass());
// throws RuntimeException: "No handler provided for this message"
```

You normally don't use this directly — it is the implicit default when you construct a `Pipeline` without arguments.
