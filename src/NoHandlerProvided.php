<?php

declare(strict_types=1);

namespace ItalyStrap\Pipeline;

final class NoHandlerProvided implements HandlerInterface
{
    public function handle(object $message): mixed
    {
        throw new \RuntimeException('No handler provided for this message');
    }
}
