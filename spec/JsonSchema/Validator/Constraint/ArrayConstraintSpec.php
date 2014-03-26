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

    function it_should_support_nested_schema_validation()
    {
        $this->getNestedSchemaValidation()->shouldReturn(false);

        $this->setNestedSchemaValidation(true);
        $this->getNestedSchemaValidation()->shouldReturn(true);
    }

    function it_should_support_internal_type_validation()
    {
        $this->setInternalType('string');
        $this->getInternalType()->shouldReturn('string');
    }

    function it_should_allow_uniqueness()
    {
        $this->getUniqueness()->shouldReturn(false);

        $this->setUniqueness(true);
        $this->getUniqueness()->shouldReturn(true);
    }

    function it_should_support_min_count()
    {
        $this->setMinimumCount(10);
        $this->getMinimumCount()->shouldReturn(10);
    }
}