<?php

namespace JsonSchema\Validator\ErrorHandler;

trait HasErrorHandlerTrait
{
    public function getErrors()
    {
        $listeners = $this->getEventDispatcher()->getListeners('validation.error');

        if (empty($listeners)) {
            return false;
        }

        $errors = [];

        foreach ($listeners as $listener) {
            if (isset($listener[0])
                && $listener[0] instanceof ErrorHandlerInterface
            ) {
                $error = $listener[0]->getErrors();
                if (!empty($error)) {
                    $errors[] = $error;
                }
            }
        }

        return $errors;
    }

    public function getErrorCount()
    {
        return count($this->getErrors());
    }
}