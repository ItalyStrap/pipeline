# Pipeline

The main pipeline dispatcher. It chains middleware in order and delegates to a handler when the middleware stack is exhausted.

## Construction

```php
use ItalyStrap\Pipeline\Pipeline;
use ItalyStrap\Pipeline\CallbackHandler;

// With a handler and middleware
$handler = new Pipeline(
    new CallbackHandler(static fn (object $msg): string => 'done'),
    new LoggingMiddleware(),
    new ValidationMiddleware(),
);

// With only a handler (no middleware)
$handler = new Pipeline(new CallbackHandler(static fn (object $msg): string => 'done'));

// Without a handler — throws RuntimeException if no middleware handles the message
$handler = new Pipeline();
```

The first argument is the handler (defaults to `NoHandlerProvided` which throws `\RuntimeException('No handler provided for this message')`). All subsequent arguments are middleware, processed in order (FIFO).

## Dispatching

```php
$result = $handler->handle($message);
```

The message flows through each middleware in order. Each middleware calls `$handler->handle($message)` to continue to the next one. When all middlewares have been executed, the handler is called.

## Single-Use Guarantee

A `Pipeline` instance can only dispatch **once**. Calling `handle()` a second time throws:

```php
$handler = new Pipeline(new CallbackHandler(static fn (object $msg): string => 'ok'));
$handler->handle(new \stdClass()); // works

$handler->handle(new \stdClass()); // throws RuntimeException:
                                    // "This pipeline instance has already been dispatched"
```

This is intentional — it mirrors the HTTP middleware model where a request flows through a handler stack exactly once. The middleware array is consumed during dispatch, so reusing the instance would silently skip all middleware.

**To dispatch multiple messages**, create a new `Pipeline` for each:

```php
function createPipeline(): Pipeline
{
    return new Pipeline(
        new CreateUserHandler(),
        new LoggingMiddleware(),
        new ValidationMiddleware(),
    );
}

createPipeline()->handle($messageA);
createPipeline()->handle($messageB);
```

## Execution Flow

```
handle($message)
  │
  ├─ Guard: throw if already dispatched
  │
  ├─ Middleware #1 → process($message, nextHandler)
  │                    │
  │                    └─ Middleware #2 → process($message, nextHandler)
  │                                        │
  │                                        └─ ... → Fallback Handler
  │
  └─ Return result
```
