<?php

namespace spec\JsonSchema\Validator\Constraint;

use JsonSchema\Validator\ErrorHandler\BufferErrorHandler;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ObjectConstraintSpec extends ObjectBehavior
{
    function let(\stdClass $value, BufferErrorHandler $handler)
    {
        $this->beConstructedWith($value, $handler);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('JsonSchema\Validator\Constraint\ObjectConstraint');
        $this->shouldImplement('JsonSchema\Validator\Constraint\ConstraintInterface');
    }

    function it_should_pass_object_types(\stdClass $value)
    {
        $this->setValue($value);
        $this->shouldHaveCorrectType();
        $this->validateType()->shouldReturn(true);
    }

    function it_should_fail_non_object_types()
    {
        $this->setValue([]);
        $this->shouldNotHaveCorrectType();
        $this->validateType()->shouldReturn(false);
    }

    function it_should_support_schema_validation()
    {
        $this->getSchemaValidation()->shouldReturn(false);

        $this->setSchemaValidation(true);
        $this->getSchemaValidation()->shouldReturn(true);
    }

    function it_should_support_nested_schema_validation()
    {
        $this->setNestedSchemaValidation(true);
        $this->getNestedSchemaValidation()->shouldReturn(true);
    }

    function it_should_support_nested_regex_validation()
    {
        $this->setNestedRegexValidation(true);
        $this->getNestedRegexValidation()->shouldReturn(true);
    }

    function it_should_support_custom_dependency_validation()
    {
        $this->setDependencyValidation(true);
        $this->getDependencyValidation()->shouldReturn(true);
    }
}