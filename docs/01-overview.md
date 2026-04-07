# Overview

## What It Is

`italystrap/pipeline` implements the **middleware pipeline** pattern: a message object flows through an ordered list of middleware, each of which can inspect, modify, or short-circuit the message before it reaches a final handler.

```
Message → Middleware #1 → Middleware #2 → ... → Fallback Handler → Result
```

This is the same pattern used by PSR-15 HTTP middleware, adapted for any message type.

## Design Principles

### Generic Messages

The pipeline accepts any `object` as a message. There is no base class or marker interface to implement — your commands, queries, or events are plain PHP objects:

```php
class CreateUser
{
    public function __construct(
        public readonly string $name,
        public readonly string $email,
    ) {
    }
}
```

### Single-Use Dispatch

A `Pipeline` instance is designed for **one dispatch only**, following the same philosophy as HTTP request handlers. After `handle()` completes, calling it again throws a `\RuntimeException`. Create a new `Pipeline` for each dispatch cycle.

### Zero Dependencies

The package requires nothing beyond PHP 8.2. No framework, no PSR packages, no external libraries.

## Components

| Class | Role |
|---|---|
| `HandlerInterface` | Contract for anything that handles a message |
| `MiddlewareInterface` | Contract for anything that processes a message and delegates to the next handler |
| `Pipeline` | The pipeline dispatcher — chains middleware and a handler |
| `CallbackHandler` | Wraps a `callable` as a `HandlerInterface` |
| `DecorateHandler` | Immutable decorator composing one middleware with one handler |
| `NoHandlerProvided` | Default handler that throws when no real handler is given |
