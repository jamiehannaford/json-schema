<?php

namespace JsonSchema\Validator;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

interface ValidatorInterface
{
    public function setData($data);

    public function setErrorHandler(EventSubscriberInterface $handler);

    public function validate();
}