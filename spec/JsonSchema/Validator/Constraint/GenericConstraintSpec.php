<?php

namespace spec\JsonSchema\Validator\Constraint;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcher;

class GenericConstraintSpec extends ObjectBehavior
{
    function let(\stdClass $value, EventDispatcher $dispatcher)
    {
        $this->beConstructedWith('Foo', $value, $dispatcher);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('JsonSchema\Validator\Constraint\GenericConstraint');
        $this->shouldImplement('JsonSchema\Validator\Constraint\ConstraintInterface');
    }

    function it_should_always_have_correct_type()
    {
        $this->hasCorrectType()->shouldReturn(true);
    }

    function it_should_always_validate_successfully_if_there_are_no_conditions()
    {
        $this->validateConstraint()->shouldReturn(true);
    }
}
