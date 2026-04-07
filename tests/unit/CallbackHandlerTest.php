<?php

declare(strict_types=1);

namespace ItalyStrap\Pipeline\Tests\Unit;

use ItalyStrap\Pipeline\CallbackHandler;
use ItalyStrap\Pipeline\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\Test;

final class CallbackHandlerTest extends UnitTestCase
{
    #[Test]
    public function itDelegatesToCallable(): void
    {
        $handler = new CallbackHandler(static fn (object $message): string => 'result');

        $result = $handler->handle(new \stdClass());

        $this->assertSame('result', $result);
    }

    #[Test]
    public function itPassesTheMessageToCallable(): void
    {
        $msg = new class() {
            public function getValue(): string
            {
                return 'hello';
            }
        };

        $handler = new CallbackHandler(static function (object $message): string {
            \assert(\method_exists($message, 'getValue'));
            $value = $message->getValue();
            \assert(\is_string($value));
            return $value;
        });

        $result = $handler->handle($msg);

        $this->assertSame('hello', $result);
    }
}
