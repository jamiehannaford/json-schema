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
        $this->setDependenciesValidation(true);
        $this->getDependenciesValidation()->shouldReturn(true);
    }

    function it_should_validate_schema()
    {
        $value = (object)['title' => 101];
        $this->setValue($value);

        $this->setSchemaValidation(true);

        $this->validate()->shouldReturn(false);
    }

    function it_should_validate_nested_schema()
    {
        $value = (object)[
            'foo' => (object)['title' => 101]
        ];
        $this->setValue($value);

        $this->setSchemaValidation(false);
        $this->setNestedSchemaValidation(true);

        $this->validate()->shouldReturn(false);
    }

    function it_should_fail_if_pattern_properties_keys_are_not_valid_regex_strings()
    {
        $schema = (object)['title' => 'foo'];

        $this->setPatternPropertiesValidation(true);
        $this->setValue((object) ['#incomplete-regex' => $schema]);
        $this->validate()->shouldReturn(false);
    }

    function it_should_fail_if_pattern_properties_vals_are_not_valid_schemas()
    {
        $schema = (object)['title' => 101];

        $this->setPatternPropertiesValidation(true);
        $this->setValue((object) ['#complete-regex#' => $schema]);
        $this->validate()->shouldReturn(false);
    }

    function it_should_fail_if_dependencies_vals_are_not_array_or_object()
    {
        $value = (object)['foo' => 'bar'];

        $this->setValue($value);
        $this->setDependenciesValidation(true);
        $this->validate()->shouldReturn(false);
    }

    function it_should_fail_if_dependencies_object_vals_are_not_valid_schemas()
    {
        $value = (object)[
            'foo' => (object)['title' => 101]
        ];

        $this->setValue($value);
        $this->setDependenciesValidation(true);
        $this->validate()->shouldReturn(false);
    }

    function it_should_fail_if_dependencies_array_vals_are_not_strings()
    {
        $value = (object)[
            'foo' => [1, 2, 3]
        ];

        $this->setValue($value);
        $this->setDependenciesValidation(true);
        $this->validate()->shouldReturn(false);
    }

    function it_should_fail_if_dependencies_array_vals_do_not_have_at_least_1_member()
    {
        $value = (object)[
            'foo' => []
        ];

        $this->setValue($value);
        $this->setDependenciesValidation(true);
        $this->validate()->shouldReturn(false);
    }
}