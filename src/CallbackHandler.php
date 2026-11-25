<?php

declare(strict_types=1);

namespace ItalyStrap\Pipeline;

/**
 * @template TMessage of object
 * @implements HandlerInterface<TMessage>
 */
final class CallbackHandler implements HandlerInterface
{
    /**
     * @var callable(TMessage): mixed
     */
    private $callback;

    /**
     * @param callable(TMessage): mixed $callback
     */
    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    /**
     * @param TMessage $message
     */
    public function handle(object $message): mixed
    {
        return ($this->callback)($message);
    }
}
