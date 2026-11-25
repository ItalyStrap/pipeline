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
4. Accepts only the messages of the application, here the ones implementing its own `MessageInterface`.

```php
<?php

declare(strict_types=1);

namespace App\Infrastructure;

use ItalyStrap\Pipeline\Pipeline;
use ItalyStrap\Pipeline\CallbackHandler;
use ItalyStrap\Pipeline\HandlerInterface;
use ItalyStrap\Pipeline\MiddlewareInterface;
use Symfony\Component\Console\Command\Command;

/**
 * @implements HandlerInterface<object>
 */
final class ConsoleHandler implements HandlerInterface
{
    /**
     * @var Pipeline<MessageInterface>
     */
    private Pipeline $pipeline;

    /**
     * @param MiddlewareInterface<MessageInterface> ...$middleware
     */
    public function __construct(MiddlewareInterface ...$middleware)
    {
        $this->pipeline = new Pipeline(
            new CallbackHandler(
                static fn (MessageInterface $message): int => Command::SUCCESS
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

The wrapper accepts any `object` and checks it once at the entry, while its middleware declare the message they work with, see [Typed Messages](07-typed-messages.md).

## Real-World Example

A CLI tool, for example a scaffolding tool built on Symfony Console, uses the wrapper like this:

```php
$handler = new ConsoleHandler(
    new GenerateFilesMiddleware(),
    new ValidateConfigMiddleware(),
);

$exitCode = $handler->handle($message); // always int
```

The pipeline mechanics live in the reusable `Pipeline` package. The CLI-specific contract lives in the wrapper. Neither knows about the other's internals.
