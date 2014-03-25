<?php

namespace spec\JsonSchema\Validator\Constraint;

use JsonSchema\Validator\ErrorHandler\BufferErrorHandler;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class BooleanConstraintSpec extends ObjectBehavior
{
    function let(BufferErrorHandler $handler)
    {
        $this->beConstructedWith(true, $handler);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('JsonSchema\Validator\Constraint\BooleanConstraint');
        $this->shouldImplement('JsonSchema\Validator\Constraint\ConstraintInterface');
    }

    function it_should_pass_bool_types()
    {
        $this->setValue(true);
        $this->shouldHaveCorrectType();
        $this->validate()->shouldReturn(true);
    }

    function it_should_fail_non_bool_types()
    {
        $this->setValue('Foo');
        $this->shouldNotHaveCorrectType();
        $this->validate()->shouldReturn(false);
    }
}