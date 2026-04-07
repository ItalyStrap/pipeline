<?php

declare(strict_types=1);

namespace ItalyStrap\Pipeline\Tests\Unit;

use ItalyStrap\Pipeline\NoHandlerProvided;
use ItalyStrap\Pipeline\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\Test;

final class NoHandlerProvidedTest extends UnitTestCase
{
    #[Test]
    public function itThrowsRuntimeException(): void
    {
        $handler = new NoHandlerProvided();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('No handler provided for this message');

        $handler->handle(new \stdClass());
    }
}
