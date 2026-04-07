<?php

declare(strict_types=1);

namespace ItalyStrap\Pipeline\Tests\Stubs;

use ItalyStrap\Pipeline\CallbackHandler;
use ItalyStrap\Pipeline\HandlerInterface;
use ItalyStrap\Pipeline\MiddlewareInterface;
use ItalyStrap\Pipeline\Pipeline;

/**
 * Example wrapper that narrows the return type to int and provides a default
 * exit code of 0 (success) when no middleware handles the message.
 *
 * This demonstrates how a CLI tool (e.g. one built on Symfony Console)
 * would adapt the generic Pipeline to its specific contract.
 */
final readonly class ConsoleHandler implements HandlerInterface
{
    public const SUCCESS = 0;

    public const FAILURE = 1;

    private Pipeline $pipeline;

    public function __construct(MiddlewareInterface ...$middleware)
    {
        $this->pipeline = new Pipeline(
            new CallbackHandler(
                static fn (object $message): int => self::SUCCESS
            ),
            ...$middleware,
        );
    }

    public function handle(object $message): int
    {
        if (!$message instanceof MessageInterface) {
            throw new \InvalidArgumentException(\sprintf(
                'Expected instance of %s, got %s',
                MessageInterface::class,
                $message::class,
            ));
        }

        $result = $this->pipeline->handle($message);

        if (!is_int($result)) {
            throw new \RuntimeException(\sprintf(
                'Expected middleware to return an int exit code, got %s',
                get_debug_type($result),
            ));
        }

        return $result;
    }
}
