<?php

namespace JsonSchema\Validator;

use JsonSchema\Validator\ErrorHandler\ErrorHandlerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

trait HasEventDispatcherTrait
{
    protected $eventDispatcher;

    public function setEventDispatcher(EventDispatcherInterface $dispatcher)
    {
        $this->eventDispatcher = $dispatcher;
    }

    public function getEventDispatcher()
    {
        return $this->eventDispatcher;
    }

    public function setErrorHandler(ErrorHandlerInterface $handler)
    {
        //$this->getEventDispatcher()->addSubscriber($handler);
        $this->getEventDispatcher()->addListener('validation.error', [$handler, 'receiveError']);
    }
}