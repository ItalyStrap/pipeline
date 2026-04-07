<?php

declare(strict_types=1);

namespace ItalyStrap\Pipeline\Tests\Benchmark;

use ItalyStrap\Pipeline\CallbackHandler;
use ItalyStrap\Pipeline\HandlerInterface;
use ItalyStrap\Pipeline\MiddlewareInterface;
use ItalyStrap\Pipeline\Pipeline;

/**
 * @BeforeMethods({"setUp"})
 */
final class PipelineBench
{
    private CallbackHandler $handler;

    /**
     * @var list<MiddlewareInterface>
     */
    private array $middleware = [];

    public function setUp(): void
    {
        $this->handler = new CallbackHandler(static fn (object $message): int => 1);

        $this->middleware = [];
        for ($i = 0; $i < 10; $i++) {
            $this->middleware[] = new class() implements MiddlewareInterface {
                public function process(object $message, HandlerInterface $handler): mixed
                {
                    return $handler->handle($message);
                }
            };
        }
    }

    public function benchHandleNoMiddleware(): void
    {
        $this->createHandler(0)->handle(new \stdClass());
    }

    public function benchHandleOneMiddleware(): void
    {
        $this->createHandler(1)->handle(new \stdClass());
    }

    public function benchHandleThreeMiddleware(): void
    {
        $this->createHandler(3)->handle(new \stdClass());
    }

    public function benchHandleFiveMiddleware(): void
    {
        $this->createHandler(5)->handle(new \stdClass());
    }

    public function benchHandleTenMiddleware(): void
    {
        $this->createHandler(10)->handle(new \stdClass());
    }

    private function createHandler(int $middlewareCount = 0): Pipeline
    {
        return new Pipeline(
            $this->handler,
            ...\array_slice($this->middleware, 0, $middlewareCount),
        );
    }
}
