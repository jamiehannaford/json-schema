<?php

namespace spec\JsonSchema\Validator\Constraint;

use JsonSchema\Validator\ErrorHandler\BufferErrorHandler;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class NumberConstraintSpec extends ObjectBehavior
{
    function let(BufferErrorHandler $handler)
    {
        $this->beConstructedWith(101, $handler);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('JsonSchema\Validator\Constraint\NumberConstraint');
        $this->shouldImplement('JsonSchema\Validator\Constraint\ConstraintInterface');
    }

    function it_should_support_lower_bound()
    {
        $this->setLowerBound(10);
        $this->getLowerBound()->shouldReturn(10);
    }

    function it_should_pass_ints()
    {
        $this->setValue(0);

        $this->shouldHaveCorrectType();

        $this->validate()->shouldReturn(true);
    }

    function it_should_fail_string_types()
    {
        $this->setValue('Foo');

        $this->shouldNotHaveCorrectType();

        $this->validate()->shouldReturn(false);
    }
}