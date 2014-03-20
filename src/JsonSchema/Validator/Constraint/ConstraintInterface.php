<?php

namespace JsonSchema\Validator\Constraint;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

interface ConstraintInterface
{
    public function setValue($value);

    public function setEventDispatcher(EventDispatcherInterface $dispatcher);

    public function getEventDispatcher();

    public function validate();

    public function validateType();
}