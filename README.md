# ItalyStrap Pipeline

A lightweight, zero-dependency middleware pipeline for PHP.

Route any object through a composable pipeline of middleware before reaching a final handler — inspired by the HTTP middleware pattern (PSR-15).

## Installation

```bash
composer require italystrap/pipeline
```

## Quick Start

```php
use ItalyStrap\Pipeline\Pipeline;
use ItalyStrap\Pipeline\CallbackHandler;
use ItalyStrap\Pipeline\HandlerInterface;
use ItalyStrap\Pipeline\MiddlewareInterface;

// 1. Define a message (any object mutable or immutable)
$message = new User(name: 'Alice', email: 'alice@example.com');

// 2. Create a pipeline with a handler and middleware
$handler = new Pipeline(
    new CallbackHandler(static function (object $message): string {
        // final handler logic
        return 'User created: ' . $message->name;
    }),
    new LoggingMiddleware(),
    new ValidationMiddleware(),
);

// 3. Dispatch when ready
$result = $handler->handle($message);
```

The message flows through middleware in the order they were provided, then reaches the handler.

## Documentation

- **[Overview](docs/01-overview.md)** — Architecture, pattern, and design rationale
- **[Interfaces](docs/02-interfaces.md)** — `HandlerInterface` and `MiddlewareInterface`
- **[Pipeline](docs/03-pipeline.md)** — The main pipeline dispatcher
- **[Helpers](docs/04-helpers.md)** — `CallbackHandler`, `DecorateHandler`, `NoHandlerProvided`
- **[Pipeline vs DecorateHandler](docs/05-pipeline-vs-decorate-handler.md)** — When to use which
- **[Wrapping the Pipeline](docs/06-wrapping-the-pipeline.md)** — Adapting the generic pipeline to typed contracts (e.g. CLI exit codes)
- **[Typed Messages](docs/07-typed-messages.md)**: Generic message types for PHPStan and Psalm

## License

MIT — see [LICENSE](LICENSE).
