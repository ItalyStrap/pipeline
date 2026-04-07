<?php

declare(strict_types=1);

namespace ItalyStrap\Pipeline\Tests\Unit;

use ItalyStrap\Pipeline\CallbackHandler;
use ItalyStrap\Pipeline\DecorateHandler;
use ItalyStrap\Pipeline\HandlerInterface;
use ItalyStrap\Pipeline\MiddlewareInterface;
use ItalyStrap\Pipeline\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\Test;

final class DecorateHandlerTest extends UnitTestCase
{
    #[Test]
    public function itComposesMiddlewareAndHandler(): void
    {
        $middleware = new class() implements MiddlewareInterface {
            public function process(object $message, HandlerInterface $handler): mixed
            {
                $result = $handler->handle($message);
                \assert(\is_string($result));
                return 'middleware:' . $result;
            }
        };

        $inner = new CallbackHandler(static fn (object $message): string => 'inner');
        $decorated = new DecorateHandler($middleware, $inner);

        $result = $decorated->handle(new \stdClass());

        $this->assertSame('middleware:inner', $result);
    }
}
