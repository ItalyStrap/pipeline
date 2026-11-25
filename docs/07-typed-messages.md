# Typed Messages

## The Problem

`HandlerInterface` and `MiddlewareInterface` accept any `object`, so a pipeline can carry any message. Inside a middleware, though, you usually know the message class and want to call its methods. PHP does not allow a narrower parameter type than the interface:

```php
// Fatal error: the parameter must stay `object`
public function process(CreateUser $message, HandlerInterface $handler): mixed
```

## The Solution: Generics for Static Analysis

The interfaces and the classes are generic over the message, `TMessage`, for PHPStan and Psalm. The native type stays `object`, the PHPDoc tells the analyser which message the class works with:

```php
use ItalyStrap\Pipeline\HandlerInterface;
use ItalyStrap\Pipeline\MiddlewareInterface;

/**
 * @implements MiddlewareInterface<CreateUser>
 */
final class ValidateEmailMiddleware implements MiddlewareInterface
{
    /**
     * @param CreateUser $message
     * @param HandlerInterface<CreateUser> $handler
     */
    public function process(object $message, HandlerInterface $handler): mixed
    {
        if (!\filter_var($message->email, \FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        return $handler->handle($message);
    }
}
```

`Pipeline`, `CallbackHandler` and `DecorateHandler` infer the message from what they receive:

```php
$pipeline = new Pipeline(
    new CallbackHandler(static fn (CreateUser $message): string => $message->name . ' created'),
    new ValidateEmailMiddleware(),
);
// Pipeline<CreateUser>: passing a middleware for another message is reported by the analyser.
```

## Variance

- `HandlerInterface` is contravariant: a handler of any `object` is also a handler of `CreateUser`, so `new CallbackHandler(static fn (object $message) => ...)` fits a `Pipeline<CreateUser>`.
- `MiddlewareInterface` is invariant: a middleware both receives the message and passes it on to the next handler, so it must declare the exact message of the pipeline.

At runtime nothing changes: the types exist only for static analysis.
