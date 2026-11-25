<?php

declare(strict_types=1);

namespace ItalyStrap\Pipeline;

/**
 * @template-contravariant TMessage of object The message the handler receives:
 *                         a handler of any object also handles a narrower one.
 */
interface HandlerInterface
{
    /**
     * @param TMessage $message
     */
    public function handle(object $message): mixed;
}
