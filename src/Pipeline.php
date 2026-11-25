<?php

declare(strict_types=1);

namespace ItalyStrap\Pipeline;

/**
 * @template TMessage of object
 * @implements HandlerInterface<TMessage>
 */
final class Pipeline implements HandlerInterface
{
    /**
     * @var array<int|string, MiddlewareInterface<TMessage>>
     */
    private array $middleware;

    private bool $handled = false;

    /**
     * @param HandlerInterface<TMessage> $handler
     * @param MiddlewareInterface<TMessage> ...$middleware
     */
    public function __construct(
        private readonly HandlerInterface $handler = new NoHandlerProvided(),
        MiddlewareInterface ...$middleware,
    ) {
        $this->middleware = $middleware;
    }

    /**
     * @param TMessage $message
     */
    public function handle(object $message): mixed
    {
        if ($this->handled) {
            throw new \RuntimeException('This pipeline instance has already been dispatched');
        }

        $this->handled = true;

        return $this->next($message);
    }

    /**
     * @param TMessage $message
     */
    private function next(object $message): mixed
    {
        if ($this->middleware === []) {
            return $this->handler->handle($message);
        }

        $middleware = \array_shift($this->middleware);
        return $middleware->process($message, new CallbackHandler($this->next(...)));
    }
}
