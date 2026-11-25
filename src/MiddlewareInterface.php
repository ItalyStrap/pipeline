<?php

declare(strict_types=1);

namespace ItalyStrap\Pipeline;

/**
 * @template TMessage of object The message the middleware receives and passes on.
 */
interface MiddlewareInterface
{
    /**
     * @param TMessage $message
     * @param HandlerInterface<TMessage> $handler
     */
    public function process(object $message, HandlerInterface $handler): mixed;
}
