<?php

namespace spec\JsonSchema\Validator\Constraint;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use spec\JsonSchema\Validator\HasValidationChecker;
use Symfony\Component\EventDispatcher\EventDispatcher;

class ObjectConstraintSpec extends ObjectBehavior
{
    use HasValidationChecker;

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
        $this->setDependenciesSchemaValidation(true);
        $this->getDependenciesSchemaValidation()->shouldReturn(true);
    }

    function it_should_validate_schema()
    {
        $value = (object)['title' => 101];
        $this->setValue($value);

        $this->setSchemaValidation(true);

        $this->testFailureDispatch($value, 'Object is not a valid schema');
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

        $this->testFailureDispatch($value, 'Object contains invalid nested schemas');
        $this->validate()->shouldReturn(false);
    }

    function it_should_fail_if_pattern_properties_keys_are_not_valid_regex_strings()
    {
        $schema = (object)['title' => 'foo'];
        $invalid = '#incomplete-regex';

        $this->setPatternPropertiesValidation(true);
        $this->setValue((object) [$invalid => $schema]);

        $this->testFailureDispatch($invalid, 'Object contains keys which are invalid regular expressions');
        $this->validate()->shouldReturn(false);
    }

    function it_should_fail_if_pattern_properties_vals_are_not_valid_schemas()
    {
        $schema = (object)['title' => 101];

        $this->setPatternPropertiesValidation(true);
        $this->setValue((object) ['#complete-regex#' => $schema]);

        $this->testFailureDispatch($schema, 'Object contains values which are invalid schemas');
        $this->validate()->shouldReturn(false);
    }

    function it_should_fail_if_dependencies_vals_are_not_array_or_object()
    {
        $value = (object)['foo' => 'bar'];

        $this->setValue($value);
        $this->setDependenciesSchemaValidation(true);

        $this->testFailureDispatch('bar', 'Object values need to be either objects or array');
        $this->validate()->shouldReturn(false);
    }

    function it_should_fail_if_dependencies_object_vals_are_not_valid_schemas()
    {
        $value = (object)[
            'foo' => (object)['title' => 101]
        ];

        $this->setValue($value);
        $this->setDependenciesSchemaValidation(true);

        $this->testFailureDispatch($value, 'Objects provided as values must be valid schemas');
        $this->validate()->shouldReturn(false);
    }

    function it_should_fail_if_dependencies_array_vals_are_not_strings()
    {
        $value = (object)[
            'foo' => [1, 2, 3]
        ];

        $this->setValue($value);
        $this->setDependenciesSchemaValidation(true);

        $this->testFailureDispatch([1, 2, 3], 'The values of this array are of an invalid type', 'string');
        $this->validate()->shouldReturn(false);
    }

    function it_should_fail_if_dependencies_array_vals_do_not_have_at_least_1_member()
    {
        $value = (object)[
            'foo' => []
        ];

        $this->setValue($value);
        $this->setDependenciesSchemaValidation(true);

        $this->testFailureDispatch([], 'Array does not contain enough elements', 1);
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

    function it_should_support_strict_additional_properties()
    {
        $this->setStrictAdditionalProperties(true);
        $this->getStrictAdditionalProperties()->shouldReturn(true);
    }

    function it_should_pass_validation_if_strict_additional_properties_check_is_not_true()
    {
        $object = (object)['foo' => 'bar'];
        $this->setValue($object);

        $this->validate()->shouldReturn(true);
    }

    function it_should_support_allowed_property_names_as_array()
    {
        $names = ['foo', 'bar'];
        $this->setAllowedPropertyNames($names);
        $this->getAllowedPropertyNames()->shouldReturn($names);
    }

    function it_should_suppoert_allowed_property_names_as_object()
    {
        $properties = (object)['foo' => (object)['title' => 'bar']];
        $this->setAllowedPropertyNames($properties);
        $this->getAllowedPropertyNames()->shouldReturn(['foo']);
    }

    function it_should_deduct_items_from_object_whose_keys_match_properties()
    {
        $before = (object)['foo' => 1, 'bar' => 2, 'baz' => 3];
        $after  = (object)['foo' => 1, 'bar' => 2];

        $this->setValue($before);

        $this->setAllowedPropertyNames(['foo', 'bar']);

        $this->validateStrictProperties()->shouldReturn(false);
    }

    function it_should_support_regex_array()
    {
        $array = ['#foo#'];
        $this->setRegexArray($array);
        $this->getRegexArray()->shouldReturn($array);
    }

    function it_should_support_regex_object()
    {
        $properties = (object)['#foo#' => (object)['title' => 'bar']];
        $this->setRegexArray($properties);
        $this->getRegexArray()->shouldReturn(['#foo#']);
    }

    function it_should_throw_exception_if_regex_is_invalid()
    {
        $this->shouldThrow('InvalidArgumentException')->duringSetRegexArray(['#foo']);
    }

    function it_should_deduct_items_from_object_whose_keys_pass_patternProperties_regex()
    {
        $before = (object)['foo' => 1, '123bar' => 2, 'zoo' => 3, 'yap' => 4];

        $this->setValue($before);

        $regexes = ['#^[f|z]oo$#', '#^[1-3]{3,}\w{3,}$#'];
        $this->setRegexArray($regexes);

        $this->validateStrictProperties()->shouldReturn(false);
    }

    function it_should_fail_validation_if_additionalProperties_is_false_and_property_names_do_not_match()
    {
        $value = (object)['foo' => 1, 'bar' => 2, 'baz' => 3];
        $this->setValue($value);

        $this->setStrictAdditionalProperties(true);
        $this->setAllowedPropertyNames(['foo', 'bar']);

        $this->validate()->shouldReturn(false);
    }

    function it_should_pass_validation_if_property_names_match()
    {
        $value = (object)['foo' => 1, 'bar' => 2, 'baz' => 3];
        $this->setValue($value);

        $this->setStrictAdditionalProperties(true);
        $this->setAllowedPropertyNames(['foo', 'bar', 'baz']);

        $this->validate()->shouldReturn(true);
    }

    function it_should_fail_validation_if_additionalProperties_is_false_and_patternProperty_regexes_fail()
    {
        $value = (object)['foo' => 1, 'bar' => 2, 'baz' => 3];
        $this->setValue($value);

        $this->setStrictAdditionalProperties(true);

        $regexes = ['#^[f|z]oo$#'];
        $this->setRegexArray($regexes);

        $this->validate()->shouldReturn(false);
    }

    function it_should_pass_validation_if_regex_names_match()
    {
        $value = (object)['foo' => 1, 'bar' => 2, 'baz' => 3];
        $this->setValue($value);

        $this->setStrictAdditionalProperties(true);

        $regexes = ['#^foo$#', '#^ba[r|z]$#'];
        $this->setRegexArray($regexes);

        $this->validate()->shouldReturn(true);
    }

    function it_should_support_schema_dependencies()
    {
        $schemas = (object)['foo' => (object)['title' => 'foo']];
        $this->setSchemaDependencies($schemas);
        $this->getSchemaDependencies()->shouldReturn($schemas);
    }

    function it_should_fail_validation_if_instance_value_does_not_validate_against_schema_dependencies_ex1()
    {
        $instance = (object)['people' =>
            (object)[
                'name'  => 'foo',
                'age'   => 100,
                'place' => 'bar'
            ]
        ];
        $this->setValue($instance);

        $subSchema = (object)['people' => (object)['minProperties' => 4]];

        $this->setDependenciesInstanceValidation(true);
        $this->setSchemaDependencies($subSchema);

        $this->validate()->shouldReturn(false);
    }

    function it_should_fail_validation_if_instance_value_does_not_validate_against_schema_dependencies_ex2()
    {
        $instance = (object)[
            'foo' => (object)['foo' => 1, 'bar' => 2]
        ];
        $this->setValue($instance);

        $subSchema = (object)['foo' => (object)['minProperties' => 4]];

        $this->setDependenciesInstanceValidation(true);
        $this->setSchemaDependencies($subSchema);

        $this->validate()->shouldReturn(false);
    }

    function it_should_pass_if_instance_value_validates_successfully_against_schema_dependencies_ex1()
    {
        $instance = (object)['values' => (object)['foo' => 1, 'bar' => 2, 'baz' => 3]];
        $this->setValue($instance);

        $empty = new \stdClass;

        $schema = (object)['values' => (object)[
            'type' => 'object',
            'properties' => ['foo' => $empty],
            'patternProperties' => ['#^ba[r|z]$#' => $empty]
        ]];

        $this->setDependenciesInstanceValidation(true);
        $this->setSchemaDependencies($schema);

        $this->validate()->shouldReturn(true);
    }

    function it_should_pass_if_instance_value_validates_successfully_against_schema_dependencies_ex2()
    {
        $instance = (object)['foo' => 'bar'];
        $this->setValue($instance);

        $schema = (object)['foo' => (object)[
            'enum' => ['bar', 'baz']
        ]];

        $this->setDependenciesInstanceValidation(true);
        $this->setSchemaDependencies($schema);

        $this->validate()->shouldReturn(true);
    }

    function it_should_fail_validation_if_object_does_not_contain_property_dependencies()
    {
        $this->setDependenciesInstanceValidation(true);
        $this->setAllowedPropertyNames(['foo', 'bar']);

        $value = (object)['foo' => 1, 'baz' => 2];
        $this->setValue($value);

        $this->validate()->shouldReturn(false);
    }

    function it_should_pass_validation_if_object_contains_required_property_dependencies()
    {
        $this->setDependenciesInstanceValidation(true);
        $this->setAllowedPropertyNames(['foo', 'bar']);

        $value = (object)['foo' => 1, 'bar' => 2, 'baz' => 3];
        $this->setValue($value);

        $this->validate()->shouldReturn(true);
    }

    public function getMatchers()
    {
        return [
            'relateTo' => function ($value, $arg) {
                    $diff = array_diff(get_object_vars($value), get_object_vars($arg));
                    if (count($diff) > 0 || count($value) != count($arg)) {
                        return false;
                    }
                    return true;
                }
        ];
    }
}