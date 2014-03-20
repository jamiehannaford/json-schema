<?php

namespace JsonSchema\Validator\ErrorHandler;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

interface ErrorHandlerInterface extends EventSubscriberInterface
{
    public function receiveError(Event $event);

    public function getErrorCount();

    public function getErrors();
}