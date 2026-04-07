<?php

declare(strict_types=1);

namespace ItalyStrap\Pipeline;

require(__DIR__ . '/vendor/autoload.php');

// =============================================================================
// Basic Pipeline Usage
// =============================================================================

echo "=== Basic Pipeline Usage ===" . PHP_EOL . PHP_EOL;

echo "--- Dispatching with a handler ---" . PHP_EOL;
$handler = new Pipeline(new CallbackHandler(static fn (object $message): string => 'Handled: ' . $message->name));
$command = new class () {
    public string $name = 'CreateUser';
};
$result = $handler->handle($command);
echo "Result: {$result}" . PHP_EOL . PHP_EOL;

echo "--- Dispatching without a handler ---" . PHP_EOL;
$handler = new Pipeline();
try {
    $handler->handle(new \stdClass());
} catch (\RuntimeException $e) {
    echo "Caught: {$e->getMessage()}" . PHP_EOL;
}

echo PHP_EOL;

// =============================================================================
// Single-Use Guard
// =============================================================================

echo "=== Single-Use Guard ===" . PHP_EOL . PHP_EOL;

$handler = new Pipeline(new CallbackHandler(static fn (object $message): string => 'ok'));
$handler->handle(new \stdClass());
echo "First dispatch: ok" . PHP_EOL;

try {
    $handler->handle(new \stdClass());
} catch (\RuntimeException $e) {
    echo "Second dispatch caught: {$e->getMessage()}" . PHP_EOL;
}

echo PHP_EOL;

// =============================================================================
// Middleware Pipeline
// =============================================================================

echo "=== Middleware Pipeline ===" . PHP_EOL . PHP_EOL;

echo "--- Middleware execution order ---" . PHP_EOL;

$handler = new Pipeline(
    new CallbackHandler(static function (object $message): string {
        echo "  [handler] Final handler reached" . PHP_EOL;
        return 'done';
    }),
    new class () implements MiddlewareInterface {
        public function process(object $message, HandlerInterface $handler): mixed
        {
            echo "  [middleware-1] Before" . PHP_EOL;
            $result = $handler->handle($message);
            echo "  [middleware-1] After" . PHP_EOL;
            return $result;
        }
    },
    new class () implements MiddlewareInterface {
        public function process(object $message, HandlerInterface $handler): mixed
        {
            echo "  [middleware-2] Before" . PHP_EOL;
            $result = $handler->handle($message);
            echo "  [middleware-2] After" . PHP_EOL;
            return $result;
        }
    },
);

$result = $handler->handle(new \stdClass());
echo "Result: {$result}" . PHP_EOL . PHP_EOL;

// =============================================================================
// Short-Circuiting
// =============================================================================

echo "=== Short-Circuiting ===" . PHP_EOL . PHP_EOL;

$handler = new Pipeline(
    new CallbackHandler(static function (object $message): string {
        echo "  [handler] This should NOT be reached" . PHP_EOL;
        return 'unreachable';
    }),
    new class () implements MiddlewareInterface {
        public function process(object $message, HandlerInterface $handler): mixed
        {
            echo "  [auth] Unauthorized — stopping pipeline" . PHP_EOL;
            return 'access-denied';
        }
    },
);

$result = $handler->handle(new \stdClass());
echo "Result: {$result}" . PHP_EOL . PHP_EOL;

// =============================================================================
// Middleware Mutating Message State
// =============================================================================

echo "=== Middleware Mutating Message State ===" . PHP_EOL . PHP_EOL;

$message = new class () {
    private int $value = 0;

    public function increment(): void
    {
        $this->value++;
    }

    public function value(): int
    {
        return $this->value;
    }
};

$handler = new Pipeline(
    new CallbackHandler(static function (object $message): int {
        echo "  [handler] Final value: {$message->value()}" . PHP_EOL;
        return $message->value();
    }),
    new class () implements MiddlewareInterface {
        public function process(object $message, HandlerInterface $handler): mixed
        {
            $message->increment();
            echo "  [middleware-1] Incremented to: {$message->value()}" . PHP_EOL;
            return $handler->handle($message);
        }
    },
    new class () implements MiddlewareInterface {
        public function process(object $message, HandlerInterface $handler): mixed
        {
            $message->increment();
            echo "  [middleware-2] Incremented to: {$message->value()}" . PHP_EOL;
            return $handler->handle($message);
        }
    },
);

$result = $handler->handle($message);
echo "Result: {$result}" . PHP_EOL . PHP_EOL;

// =============================================================================
// CallbackHandler
// =============================================================================

echo "=== CallbackHandler ===" . PHP_EOL . PHP_EOL;

$handler = new CallbackHandler(static fn (object $message): string => 'Callback handled: ' . get_class($message));
$result = $handler->handle(new \stdClass());
echo "Result: {$result}" . PHP_EOL . PHP_EOL;

// =============================================================================
// DecorateHandler — Immutable & Reusable
// =============================================================================

echo "=== DecorateHandler ===" . PHP_EOL . PHP_EOL;

echo "--- Single decoration ---" . PHP_EOL;
$decorated = new DecorateHandler(
    new class () implements MiddlewareInterface {
        public function process(object $message, HandlerInterface $handler): mixed
        {
            echo "  [logging] Before" . PHP_EOL;
            $result = $handler->handle($message);
            echo "  [logging] After" . PHP_EOL;
            return $result;
        }
    },
    new CallbackHandler(static fn (object $message): string => 'inner-result'),
);

$result = $decorated->handle(new \stdClass());
echo "Result: {$result}" . PHP_EOL . PHP_EOL;

echo "--- Nested decoration ---" . PHP_EOL;
$pipeline = new DecorateHandler(
    new class () implements MiddlewareInterface {
        public function process(object $message, HandlerInterface $handler): mixed
        {
            echo "  [outer] Before" . PHP_EOL;
            $result = $handler->handle($message);
            echo "  [outer] After" . PHP_EOL;
            return $result;
        }
    },
    new DecorateHandler(
        new class () implements MiddlewareInterface {
            public function process(object $message, HandlerInterface $handler): mixed
            {
                echo "  [inner] Before" . PHP_EOL;
                $result = $handler->handle($message);
                echo "  [inner] After" . PHP_EOL;
                return $result;
            }
        },
        new CallbackHandler(static function (object $message): string {
            echo "  [handler] Reached" . PHP_EOL;
            return 'done';
        }),
    ),
);

$result = $pipeline->handle(new \stdClass());
echo "Result: {$result}" . PHP_EOL . PHP_EOL;

echo "--- Reusability (calling handle multiple times) ---" . PHP_EOL;
$reusable = new DecorateHandler(
    new class () implements MiddlewareInterface {
        private int $callCount = 0;

        public function process(object $message, HandlerInterface $handler): mixed
        {
            $this->callCount++;
            echo "  Call #{$this->callCount}" . PHP_EOL;
            return $handler->handle($message);
        }
    },
    new CallbackHandler(static fn (object $message): string => 'ok'),
);

$reusable->handle(new \stdClass());
$reusable->handle(new \stdClass());
$reusable->handle(new \stdClass());
echo PHP_EOL;

// =============================================================================
// Composing Pipeline + DecorateHandler
// =============================================================================

echo "=== Composing Pipeline + DecorateHandler ===" . PHP_EOL . PHP_EOL;

$reusableInner = new DecorateHandler(
    new class () implements MiddlewareInterface {
        public function process(object $message, HandlerInterface $handler): mixed
        {
            echo "  [validation] Always runs" . PHP_EOL;
            return $handler->handle($message);
        }
    },
    new CallbackHandler(static function (object $message): string {
        echo "  [handler] Persisting..." . PHP_EOL;
        return 'persisted';
    }),
);

$handler = new Pipeline(
    $reusableInner,
    new class () implements MiddlewareInterface {
        public function process(object $message, HandlerInterface $handler): mixed
        {
            echo "  [request-logging] Dynamic, per-request middleware" . PHP_EOL;
            return $handler->handle($message);
        }
    },
);

$result = $handler->handle(new \stdClass());
echo "Result: {$result}" . PHP_EOL;
