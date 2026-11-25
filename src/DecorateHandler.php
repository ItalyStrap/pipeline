<?php

declare(strict_types=1);

namespace ItalyStrap\Pipeline;

/**
 * @template TMessage of object
 * @implements HandlerInterface<TMessage>
 */
final readonly class DecorateHandler implements HandlerInterface
{
    /**
     * @param MiddlewareInterface<TMessage> $middleware
     * @param HandlerInterface<TMessage> $nextHandler
     */
    public function __construct(
        private MiddlewareInterface $middleware,
        private HandlerInterface $nextHandler,
    ) {
    }

    /**
     * @param TMessage $message
     */
    public function handle(object $message): mixed
    {
        return $this->middleware->process($message, $this->nextHandler);
    }
}
