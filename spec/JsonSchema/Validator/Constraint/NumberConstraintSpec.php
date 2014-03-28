<?php

namespace spec\JsonSchema\Validator\Constraint;

use JsonSchema\Validator\ErrorHandler\BufferErrorHandler;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcher;

class NumberConstraintSpec extends ObjectBehavior
{
    function let(EventDispatcher $dispatcher)
    {
        $this->beConstructedWith(101, $dispatcher);
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

    function it_should_support_higher_bound()
    {
        $this->setHigherBound(100);
        $this->getHigherBound()->shouldReturn(100);
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

    function it_should_allow_exclusive_checks()
    {
        $this->setExclusive(false);
        $this->getExclusive()->shouldReturn(false);
    }

    function it_should_default_exclusive_to_false()
    {
        $this->getExclusive()->shouldReturn(false);
    }

    function it_should_fail_validation_for_numbers_lower_than_lower_boundary()
    {
        $this->setLowerBound(100);
        $this->setValue(80);

        $this->validate()->shouldReturn(false);
    }

    function it_should_fail_validation_for_numbers_on_boundary_if_exclusive_is_true()
    {
        $this->setLowerBound(5);
        $this->setExclusive(true);
        $this->setValue(5);

        $this->validate()->shouldReturn(false);
    }

    function it_should_support_multiple_of_validation()
    {
        $this->getMultipleOf()->shouldReturn(false);

        $this->setMultipleOf(10);
        $this->getMultipleOf()->shouldReturn(10);
    }

    function it_should_fail_validation_if_value_is_not_multiple_of()
    {
        $this->setMultipleOf(3);

        $this->setValue(55);
        $this->validate()->shouldReturn(false);

        $this->setValue(300);
        $this->validate()->shouldReturn(true);
    }
}