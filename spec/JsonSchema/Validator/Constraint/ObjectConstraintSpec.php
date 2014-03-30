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

    function it_should_support_max_properties()
    {
        $this->setMaxProperties(5);
        $this->getMaxProperties()->shouldReturn(5);
    }

    function it_should_fail_if_object_has_more_properties_than_maxProperties()
    {
        $this->setMaxProperties(2);

        $value = (object) [
            'foo' => 1, 'bar' => 2, 'baz' => 3
        ];

        $this->setValue($value);

        $this->validate()->shouldReturn(false);
    }

    function it_should_support_min_properties()
    {
        $this->setMinProperties(3);
        $this->getMinProperties()->shouldReturn(3);
    }

    function it_should_default_default_minProperties_to_0()
    {
        $this->getMinProperties()->shouldReturn(0);
    }

    function it_should_fail_if_object_has_less_properties_than_minProperties()
    {
        $this->setMinProperties(3);

        $value = (object) [
            'foo' => 1, 'bar' => 2
        ];

        $this->setValue($value);

        $this->validate()->shouldReturn(false);
    }

    function it_should_support_required_element_names()
    {
        $array = ['foo', 'bar'];
        $this->setRequiredElementNames($array);
        $this->getRequiredElementNames()->shouldReturn($array);
    }

    function it_should_fail_if_object_does_not_contain_elements_in_required_array()
    {
        $required = ['name', 'age'];
        $this->setRequiredElementNames($required);

        $value = (object) ['name' => 1, 'location' => 2, 'favouriteColour' => 3];
        $this->setValue($value);

        $this->validate()->shouldReturn(false);
    }

    function it_should_pass_if_objects_contains_all_required_keys()
    {
        $required = ['name', 'location'];
        $this->setRequiredElementNames($required);

        $value = (object)['name' => 'foo', 'location' => 'bar', 'age' => 'baz'];
        $this->setValue($value);

        $this->validate()->shouldReturn(true);
    }
}