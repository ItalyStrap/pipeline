# Interfaces

## HandlerInterface

The core contract. Anything that can handle a message implements this:

```php
namespace ItalyStrap\Pipeline;

interface HandlerInterface
{
    public function handle(object $message): mixed;
}
```

- Accepts any `object` as the message.
- Returns `mixed` — the caller decides what the result type means in their context.

### Implementing a Handler

```php
use ItalyStrap\Pipeline\HandlerInterface;

final class CreateUserHandler implements HandlerInterface
{
    public function handle(object $message): mixed
    {
        // persist user, return ID, etc.
        return $message->name . ' created';
    }
}
```

---

## MiddlewareInterface

The contract for pipeline participants. Each middleware receives the message and a reference to the next handler in the chain:

```php
namespace ItalyStrap\Pipeline;

interface MiddlewareInterface
{
    public function process(object $message, HandlerInterface $handler): mixed;
}
```

### Implementing Middleware

A middleware **must** call `$handler->handle($message)` to continue the pipeline. Omitting that call short-circuits the chain:

```php
use ItalyStrap\Pipeline\HandlerInterface;
use ItalyStrap\Pipeline\MiddlewareInterface;

final class LoggingMiddleware implements MiddlewareInterface
{
    public function process(object $message, HandlerInterface $handler): mixed
    {
        echo 'Before: ' . get_class($message) . PHP_EOL;

        $result = $handler->handle($message);

        echo 'After: ' . get_class($message) . PHP_EOL;

        return $result;
    }
}
```

### Short-Circuiting

Return early without calling `$handler->handle()` to stop the pipeline:

```php
final class AuthorizationMiddleware implements MiddlewareInterface
{
    public function process(object $message, HandlerInterface $handler): mixed
    {
        if (!$this->isAuthorized($message)) {
            throw new \RuntimeException('Unauthorized');
        }

        return $handler->handle($message);
    }
}
```
