<?php

namespace spec\JsonSchema\Validator;

use JsonSchema\Exception\InvalidTypeException;
use JsonSchema\Exception\ValidationException;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class SchemaValidatorSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('JsonSchema\Validator\SchemaValidator');
        $this->shouldImplement('JsonSchema\Validator\ValidatorInterface');
    }
}