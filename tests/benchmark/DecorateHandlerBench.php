<?php

declare(strict_types=1);

namespace ItalyStrap\Pipeline\Tests\Benchmark;

use ItalyStrap\Pipeline\CallbackHandler;
use ItalyStrap\Pipeline\DecorateHandler;
use ItalyStrap\Pipeline\HandlerInterface;
use ItalyStrap\Pipeline\MiddlewareInterface;

/**
 * @BeforeMethods({"setUp"})
 */
final class DecorateHandlerBench
{
    /**
     * @var CallbackHandler<object>
     */
    private CallbackHandler $innerHandler;

    /**
     * @var MiddlewareInterface<object>
     */
    private MiddlewareInterface $middleware;

    public function setUp(): void
    {
        $this->innerHandler = new CallbackHandler(static fn (object $message): int => 1);

        $this->middleware = new class() implements MiddlewareInterface {
            public function process(object $message, HandlerInterface $handler): mixed
            {
                return $handler->handle($message);
            }
        };
    }

    public function benchHandleNoDecoration(): void
    {
        $this->innerHandler->handle(new \stdClass());
    }

    public function benchHandleOneLayer(): void
    {
        $handler = new DecorateHandler($this->middleware, $this->innerHandler);
        $handler->handle(new \stdClass());
    }

    public function benchHandleThreeLayers(): void
    {
        $handler = new DecorateHandler(
            $this->middleware,
            new DecorateHandler(
                $this->middleware,
                new DecorateHandler(
                    $this->middleware,
                    $this->innerHandler,
                ),
            ),
        );
        $handler->handle(new \stdClass());
    }

    public function benchHandleFiveLayers(): void
    {
        $handler = $this->innerHandler;
        for ($i = 0; $i < 5; $i++) {
            $handler = new DecorateHandler($this->middleware, $handler);
        }

        $handler->handle(new \stdClass());
    }

    public function benchHandleTenLayers(): void
    {
        $handler = $this->innerHandler;
        for ($i = 0; $i < 10; $i++) {
            $handler = new DecorateHandler($this->middleware, $handler);
        }

        $handler->handle(new \stdClass());
    }

    public function benchReusabilityTenCalls(): void
    {
        $handler = new DecorateHandler($this->middleware, $this->innerHandler);
        $msg = new \stdClass();

        for ($i = 0; $i < 10; $i++) {
            $handler->handle($msg);
        }
    }
}
