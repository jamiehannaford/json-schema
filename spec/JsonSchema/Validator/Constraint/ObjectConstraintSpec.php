<?php

namespace spec\JsonSchema\Validator\Constraint;

use JsonSchema\Validator\ErrorHandler\BufferErrorHandler;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcher;

class ObjectConstraintSpec extends ObjectBehavior
{
    function let(\stdClass $value, EventDispatcher $dispatcher)
    {
        $this->beConstructedWith($value, $dispatcher);
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
        $this->setPatternPropertiesValidation(true);
        $this->getPatternPropertiesValidation()->shouldReturn(true);
    }

    function it_should_support_custom_dependency_validation()
    {
        $this->setDependencyValidation(true);
        $this->getDependencyValidation()->shouldReturn(true);
    }

    function it_should_validate_schema()
    {

    }

    function it_should_validate_nested_schema()
    {

    }

    function it_should_validate_nested_regex()
    {

    }

    function it_should_validate_pattern_properties_keys_as_valid_regex_strings()
    {
        // @todo Change to schema
        $schema = 'foo';

        $this->setPatternPropertiesValidation(true);
        $this->setValue((object) ['#incomplete-regex' => $schema]);
        $this->validate()->shouldReturn(false);
    }

    function it_should_validate_pattern_properties_vals_as_valid_schemas()
    {

    }
}