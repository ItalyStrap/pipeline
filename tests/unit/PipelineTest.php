<?php

declare(strict_types=1);

namespace ItalyStrap\Pipeline\Tests\Unit;

use ItalyStrap\Pipeline\CallbackHandler;
use ItalyStrap\Pipeline\HandlerInterface;
use ItalyStrap\Pipeline\MiddlewareInterface;
use ItalyStrap\Pipeline\Pipeline;
use ItalyStrap\Pipeline\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\Test;

final class PipelineTest extends UnitTestCase
{
    #[Test]
    public function itDelegatesToFallbackHandlerWhenNoMiddleware(): void
    {
        $fallback = new CallbackHandler(static fn (object $message): string => 'fallback');
        $handler = new Pipeline($fallback);

        $result = $handler->handle(new \stdClass());

        $this->assertSame('fallback', $result);
    }

    #[Test]
    public function itThrowsWhenNoMiddlewareAndNoFallback(): void
    {
        $handler = new Pipeline();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('No handler provided for this message');

        $handler->handle(new \stdClass());
    }

    #[Test]
    public function itProcessesMiddlewareInOrder(): void
    {
        /** @var \ArrayObject<int, string> $log */
        $log = new \ArrayObject();

        $handler = new Pipeline(
            new CallbackHandler(static function (object $message) use ($log): string {
                $log[] = 'handler';
                return 'done';
            }),
            new class($log) implements MiddlewareInterface {
                /**
                 * @param \ArrayObject<int, string> $log
                 */
                public function __construct(
                    private \ArrayObject $log
                ) {
                }

                public function process(object $message, HandlerInterface $handler): mixed
                {
                    $this->log[] = 'middleware-1';
                    return $handler->handle($message);
                }
            },
            new class($log) implements MiddlewareInterface {
                /**
                 * @param \ArrayObject<int, string> $log
                 */
                public function __construct(
                    private \ArrayObject $log
                ) {
                }

                public function process(object $message, HandlerInterface $handler): mixed
                {
                    $this->log[] = 'middleware-2';
                    return $handler->handle($message);
                }
            },
        );

        $result = $handler->handle(new \stdClass());

        $this->assertSame('done', $result);
        $this->assertSame(['middleware-1', 'middleware-2', 'handler'], $log->getArrayCopy());
    }

    #[Test]
    public function middlewareCanShortCircuit(): void
    {
        $handler = new Pipeline(
            new CallbackHandler(static fn (object $message): string => 'should-not-reach'),
            new class() implements MiddlewareInterface {
                public function process(object $message, HandlerInterface $handler): mixed
                {
                    return 'short-circuited';
                }
            },
        );

        $result = $handler->handle(new \stdClass());

        $this->assertSame('short-circuited', $result);
    }

    #[Test]
    public function middlewareCanBePassedVariadically(): void
    {
        /** @var \ArrayObject<int, string> $log */
        $log = new \ArrayObject();

        $m1 = new class($log) implements MiddlewareInterface {
            /**
             * @param \ArrayObject<int, string> $log
             */
            public function __construct(
                private \ArrayObject $log
            ) {
            }

            public function process(object $message, HandlerInterface $handler): mixed
            {
                $this->log[] = 'a';
                return $handler->handle($message);
            }
        };

        $m2 = new class($log) implements MiddlewareInterface {
            /**
             * @param \ArrayObject<int, string> $log
             */
            public function __construct(
                private \ArrayObject $log
            ) {
            }

            public function process(object $message, HandlerInterface $handler): mixed
            {
                $this->log[] = 'b';
                return $handler->handle($message);
            }
        };

        $handler = new Pipeline(
            new CallbackHandler(static fn (object $m): int => 1),
            $m1,
            $m2,
        );

        $handler->handle(new \stdClass());

        $this->assertSame(['a', 'b'], $log->getArrayCopy());
    }

    #[Test]
    public function middlewareCanMutateMessageState(): void
    {
        $message = new class() {
            private int $value = 0;

            public function changeValue(): void
            {
                $this->value++;
            }

            public function value(): int
            {
                return $this->value;
            }
        };

        $handler = new Pipeline(
            new CallbackHandler(static fn (object $message): int => 0),
            new class() implements MiddlewareInterface {
                public function process(object $message, HandlerInterface $handler): mixed
                {
                    if (\method_exists($message, 'changeValue')) {
                        $message->changeValue();
                    }

                    return $handler->handle($message);
                }
            },
            new class() implements MiddlewareInterface {
                public function process(object $message, HandlerInterface $handler): mixed
                {
                    if (\method_exists($message, 'changeValue')) {
                        $message->changeValue();
                    }

                    return $handler->handle($message);
                }
            },
        );

        $result = $handler->handle($message);

        $this->assertSame(0, $result);
        $this->assertSame(2, $message->value());
    }

    #[Test]
    public function itThrowsWhenHandledTwice(): void
    {
        $handler = new Pipeline(new CallbackHandler(static fn (object $message): string => 'ok'));

        $handler->handle(new \stdClass());

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('This pipeline instance has already been dispatched');

        $handler->handle(new \stdClass());
    }
}
