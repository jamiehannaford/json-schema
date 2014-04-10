<?php

namespace JsonSchema\Validator;

use JsonSchema\ArrayAccessTrait;
use Symfony\Component\EventDispatcher\Event;

class FailureEvent extends Event implements \ArrayAccess
{
    use ArrayAccessTrait;

    public function __construct(array $data = [])
    {
        $this->setData($data);
    }

    public function setData(array $data)
    {
        $this->data = $data;
    }
}
