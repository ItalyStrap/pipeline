<?php

declare(strict_types=1);

namespace ItalyStrap\Pipeline\Tests\Unit;

use ItalyStrap\Pipeline\HandlerInterface;
use ItalyStrap\Pipeline\MiddlewareInterface;
use ItalyStrap\Pipeline\Tests\Stubs\ConsoleHandler;
use ItalyStrap\Pipeline\Tests\Stubs\MessageInterface;
use ItalyStrap\Pipeline\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\Test;

final class ConsoleHandlerTest extends UnitTestCase
{
    #[Test]
    public function itReturnsSuccessWithNoMiddleware(): void
    {
        $handler = new ConsoleHandler();

        $result = $handler->handle($this->createMessage());

        $this->assertSame(ConsoleHandler::SUCCESS, $result);
    }

    #[Test]
    public function itReturnsIntFromMiddleware(): void
    {
        $handler = new ConsoleHandler(
            new /** @implements MiddlewareInterface<MessageInterface> */ class() implements MiddlewareInterface {
                public function process(object $message, HandlerInterface $handler): mixed
                {
                    return 42;
                }
            },
        );

        $result = $handler->handle($this->createMessage());

        $this->assertSame(42, $result);
    }

    #[Test]
    public function itProcessesMiddlewareChainAndReturnsInt(): void
    {
        $handler = new ConsoleHandler(
            new /** @implements MiddlewareInterface<MessageInterface> */ class() implements MiddlewareInterface {
                public function process(object $message, HandlerInterface $handler): mixed
                {
                    return $handler->handle($message);
                }
            },
            new /** @implements MiddlewareInterface<MessageInterface> */ class() implements MiddlewareInterface {
                public function process(object $message, HandlerInterface $handler): mixed
                {
                    return $handler->handle($message);
                }
            },
        );

        $result = $handler->handle($this->createMessage());

        $this->assertSame(ConsoleHandler::SUCCESS, $result);
    }

    #[Test]
    public function middlewareCanMutateMessageAndReturnSuccess(): void
    {
        $message = new class() implements MessageInterface {
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

        $handler = new ConsoleHandler(
            new /** @implements MiddlewareInterface<MessageInterface> */ class() implements MiddlewareInterface {
                public function process(object $message, HandlerInterface $handler): mixed
                {
                    if (\method_exists($message, 'changeValue')) {
                        $message->changeValue();
                    }

                    return $handler->handle($message);
                }
            },
            new /** @implements MiddlewareInterface<MessageInterface> */ class() implements MiddlewareInterface {
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

        $this->assertSame(ConsoleHandler::SUCCESS, $result);
        $this->assertSame(2, $message->value());
    }

    #[Test]
    public function middlewareCanShortCircuitWithFailure(): void
    {
        $handler = new ConsoleHandler(
            new /** @implements MiddlewareInterface<MessageInterface> */ class() implements MiddlewareInterface {
                public function process(object $message, HandlerInterface $handler): mixed
                {
                    return ConsoleHandler::FAILURE;
                }
            },
        );

        $result = $handler->handle($this->createMessage());

        $this->assertSame(ConsoleHandler::FAILURE, $result);
    }

    #[Test]
    public function itThrowsOnSecondDispatch(): void
    {
        $handler = new ConsoleHandler();

        $handler->handle($this->createMessage());

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('This pipeline instance has already been dispatched');

        $handler->handle($this->createMessage());
    }

    #[Test]
    public function itImplementsHandlerInterface(): void
    {
        $handler = new ConsoleHandler();

        $this->assertInstanceOf(HandlerInterface::class, $handler);
    }

    #[Test]
    public function itThrowsOnInvalidMessageType(): void
    {
        $handler = new ConsoleHandler();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected instance of ' . MessageInterface::class);

        $handler->handle(new \stdClass());
    }

    private function createMessage(): MessageInterface
    {
        return new class() implements MessageInterface {
        };
    }
}
