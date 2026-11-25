<?php

declare(strict_types=1);

namespace ItalyStrap\Pipeline\Tests\Types;

use ItalyStrap\Pipeline\CallbackHandler;
use ItalyStrap\Pipeline\DecorateHandler;
use ItalyStrap\Pipeline\HandlerInterface;
use ItalyStrap\Pipeline\MiddlewareInterface;
use ItalyStrap\Pipeline\Pipeline;

use function PHPStan\Testing\assertType;

/**
 * Analysed by PHPStan only: a pipeline keeps the type of the message it carries.
 */
final class Order
{
    public function total(): int
    {
        return 10;
    }
}

/**
 * @implements MiddlewareInterface<Order>
 */
final class DiscountMiddleware implements MiddlewareInterface
{
    /**
     * @param Order $message
     * @param HandlerInterface<Order> $handler
     */
    public function process(object $message, HandlerInterface $handler): mixed
    {
        assertType(Order::class, $message);

        return $message->total() > 5 ? $handler->handle($message) : 0;
    }
}

function typedPipeline(): void
{
    $handler = new CallbackHandler(static fn (Order $order): int => $order->total());
    assertType('ItalyStrap\Pipeline\CallbackHandler<ItalyStrap\Pipeline\Tests\Types\Order>', $handler);

    $pipeline = new Pipeline($handler, new DiscountMiddleware());
    assertType('ItalyStrap\Pipeline\Pipeline<ItalyStrap\Pipeline\Tests\Types\Order>', $pipeline);

    $decorated = new DecorateHandler(new DiscountMiddleware(), $handler);
    assertType('ItalyStrap\Pipeline\DecorateHandler<ItalyStrap\Pipeline\Tests\Types\Order>', $decorated);
}
