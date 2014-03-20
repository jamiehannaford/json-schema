<?php

namespace JsonSchema\Validator\ErrorHandler;

trait HasErrorHandlerTrait
{
    public function setErrorHandler(ErrorHandlerInterface $handler)
    {
        //$this->getEventDispatcher()->addSubscriber($handler);
        $this->getEventDispatcher()->addListener('validation.error', [$handler, 'receiveError']);
    }

    public function getErrors()
    {
        $listeners = $this->getEventDispatcher()->getListeners('validation.error');

        $errors = [];

        foreach ($listeners as $listener) {
            if (isset($listener[0])
                && $listener[0] instanceof ErrorHandlerInterface
            ) {
                $errors[] = $listener[0]->getErrors();
            }
        }

        return $errors;
    }

    public function getErrorCount()
    {
        return count($this->getErrors());
    }
}