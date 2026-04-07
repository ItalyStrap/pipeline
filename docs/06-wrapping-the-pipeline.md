# Wrapping the Pipeline

## The Problem

The `Pipeline` class handles any `object` message and returns a `mixed` value. In some contexts your application needs **stricter contracts** — a specific return type.

For example, a CLI tool built with Symfony Console expects command handlers to return `int` (an exit code) and default to `Command::SUCCESS` (0) when nothing goes wrong. The generic `Pipeline` does not enforce any of this.

You could cast the result at every call site, but that scatters framework-specific knowledge throughout your codebase. A wrapper centralizes the adaptation in one place.

## The Solution: a Typed Wrapper

Create a thin wrapper that:

1. Composes a `Pipeline` internally.
2. Accepts middleware via its own constructor.
3. Narrows the return type and injects the default.

```php
<?php

declare(strict_types=1);

namespace App\Infrastructure;

use ItalyStrap\Pipeline\Pipeline;
use ItalyStrap\Pipeline\CallbackHandler;
use ItalyStrap\Pipeline\HandlerInterface;
use ItalyStrap\Pipeline\MiddlewareInterface;
use Symfony\Component\Console\Command\Command;

final class ConsoleHandler implements HandlerInterface
{
    private Pipeline $pipeline;

    public function __construct(MiddlewareInterface ...$middleware)
    {
        $this->pipeline = new Pipeline(
            new CallbackHandler(
                static fn (object $message): int => Command::SUCCESS
            ),
            ...$middleware,
        );
    }

    public function handle(object $message): int
    {
        if (! $message instanceof MessageInterface) {
            throw new \InvalidArgumentException('Expected instance of MessageInterface');
        }
    
        return (int) $this->pipeline->handle($message);
    }
}
```

## Real-World Example: Bone's ConsoleHandler

The `theme-json-generator` package (a CLI scaffolding tool for the `theme.json` file) for example uses `italystrap/pipeline` like this:

```php
$handler = new ConsoleHandler(
    new GenerateFilesMiddleware(),
    new ValidateConfigMiddleware(),
);

$exitCode = $handler->handle($message); // always int
```

The pipeline mechanics live in the reusable `Pipeline` package. The CLI-specific contract lives in the wrapper. Neither knows about the other's internals.
