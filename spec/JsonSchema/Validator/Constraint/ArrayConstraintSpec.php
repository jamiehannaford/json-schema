<?php

namespace spec\JsonSchema\Validator\Constraint;

use JsonSchema\Validator\ErrorHandler\BufferErrorHandler;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ArrayConstraintSpec extends ObjectBehavior
{
    function let(BufferErrorHandler $handler)
    {
        $this->beConstructedWith([], $handler);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('JsonSchema\Validator\Constraint\ArrayConstraint');
        $this->shouldImplement('JsonSchema\Validator\Constraint\ConstraintInterface');
    }

    function it_should_pass_array_types()
    {
        $this->setValue([]);
        $this->shouldHaveCorrectType();
        $this->validateType()->shouldReturn(true);
    }

    function it_should_fail_non_array_types()
    {
        $this->setValue('Foo');
        $this->shouldNotHaveCorrectType();
        $this->validateType()->shouldReturn(false);
    }
}