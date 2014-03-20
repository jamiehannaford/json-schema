<?php

namespace JsonSchema\Validator\ErrorHandler;

use Symfony\Component\EventDispatcher\Event;

class BufferErrorHandler extends AbstractErrorHandler
{
    public function receiveError(Event $event)
    {
        $this->errors[] = $event;
    }
}