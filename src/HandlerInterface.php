<?php

declare(strict_types=1);

namespace ItalyStrap\Pipeline;

interface HandlerInterface
{
    public function handle(object $message): mixed;
}
