<?php

declare(strict_types=1);

namespace ItalyStrap\Pipeline;

interface MiddlewareInterface
{
    public function process(object $message, HandlerInterface $handler): mixed;
}
