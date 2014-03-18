<?php

namespace spec\JsonSchema\Schema;

use JsonSchema\Exception\InvalidTypeException;
use JsonSchema\Schema\AbstractSchema;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Prophecy\Exception\InvalidArgumentException;

class AbstractSchemaSpec extends ObjectBehavior
{
    const RANDOM_STRING = 'Foo';
    const TYPE_EXCEPTION = 'JsonSchema\Exception\InvalidTypeException';

    /**
     * Test that a number is greater than a minimum
     *
     * @param $keyword Name of keyword
     * @param $min     Integer the value must be greater than
     */
    private function testNumberIsGreaterThan($keyword, $min = 0)
    {
        // Make a random int less than the given minimum
        $wrongInt = ($min - rand(1, 10));

        // Test that out of bounds integers are caught
        $exception = new \InvalidArgumentException(sprintf(
            "\"%s\" must be a positive integer greater than %d, you provided %d",
            $keyword, $min, $wrongInt
        ));
        $this->shouldThrow($exception)->duringOffsetSet($keyword, $wrongInt);
    }

    /**
     * Test that incorrect data types are caught
     *
     * @param $keyword Name of keyword
     */
    private function testNonNumericTypeThrowsException($keyword)
    {
        $wrongTypes = [
            [], new \stdClass, false, fopen('php://temp', 'r+')
        ];

        $wrongVal = $wrongTypes[rand(0, count($wrongTypes) - 1)];
        $exception = InvalidTypeException::factory($keyword, $wrongVal, 'numeric value');
        $this->shouldThrow($exception)->duringOffsetSet($keyword, $wrongVal);
    }

    function let()
    {
        $this->beAnInstanceOf('spec\JsonSchema\Schema\TestableAbstractSchema');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('JsonSchema\Schema\AbstractSchema');
        $this->shouldImplement('JsonSchema\Schema\SchemaInterface');
        $this->shouldImplement('\ArrayAccess');
    }

    function it_should_have_a_mutable_title_in_string_form()
    {
        $this->offsetSet('title', self::RANDOM_STRING);
        $this->offsetGet('title')->shouldReturn(self::RANDOM_STRING);

        $this->offsetSet('title', false);
        $this->offsetGet('title')->shouldBeString();

        $this->offsetSet('title', 2345);
        $this->offsetGet('title')->shouldBeString();
    }

    function it_should_have_a_mutable_desc_in_string_form()
    {
        $this->offsetSet('description', self::RANDOM_STRING);
        $this->offsetGet('description')->shouldReturn(self::RANDOM_STRING);

        $this->offsetSet('description', null);
        $this->offsetGet('description')->shouldBeString();
    }

    function it_should_throw_exception_if_casting_val_to_string_is_impossible()
    {
        $this->shouldThrow('\InvalidArgumentException')->duringOffsetSet('description', []);

        $exception = new \InvalidArgumentException('"description" must be a string, you provided an object');
        $this->shouldThrow($exception)->duringOffsetSet('description', new \stdClass());

        $exception = new \InvalidArgumentException('"title" must be a string, you provided a resource');
        $resource = fopen('php://temp', 'r+');
        $this->shouldThrow($exception)->duringOffsetSet('title', $resource);
        fclose($resource);
    }

    function it_should_support_multipleOf_keyword()
    {
        $this->offsetSet('multipleOf', 50);
        $this->offsetGet('multipleOf')->shouldReturn(50);
    }

    function it_should_validate_multipleOf_keyword_as_natural_number()
    {
        $this->testNonNumericTypeThrowsException('multipleOf');
        $this->testNumberIsGreaterThan('multipleOf');
    }

    function it_should_validate_maximum_keyword_as_numeric_type()
    {
        $this->testNonNumericTypeThrowsException('maximum');
    }

    function it_should_support_exclusiveMaximum_keyword()
    {
        $this->offsetSet('exclusiveMaximum', true);
        $this->offsetGet('exclusiveMaximum')->shouldReturn(true);

        $this->offsetSet('exclusiveMaximum', 0);
        $this->offsetGet('exclusiveMaximum')->shouldReturn(false);
    }

    function it_should_validate_minimum_keyword_as_numeric_type()
    {
        $this->testNonNumericTypeThrowsException('minimum');
    }

    function it_should_support_exclusiveMinimum_keyword()
    {
        $this->offsetSet('exclusiveMinimum', true);
        $this->offsetGet('exclusiveMinimum')->shouldReturn(true);

        $this->offsetSet('exclusiveMinimum', 0);
        $this->offsetGet('exclusiveMinimum')->shouldReturn(false);
    }

    function it_should_throw_exception_when_setting_minLength_without_natural_number_or_zero()
    {
        $this->testNonNumericTypeThrowsException('minLength');
        $this->testNumberIsGreaterThan('minLength', -1);
    }

    function it_should_throw_exception_when_setting_maxLength_without_natural_number_or_zero()
    {
        $this->testNonNumericTypeThrowsException('maxLength');
        $this->testNumberIsGreaterThan('maxLength', -1);
    }

    function it_should_throw_exception_if_casting_pattern_to_string_is_impossible()
    {
        $exception = new \InvalidArgumentException('"pattern" must be a string, you provided an array');
        $this->shouldThrow($exception)->duringOffsetSet('pattern', []);
    }

    function it_should_throw_exception_if_pattern_is_invalid_regex()
    {
        $this->shouldThrow('\InvalidArgumentException')->duringOffsetSet('pattern', '#missing-delimeter');
    }

    function it_should_only_let_additionalItems_be_a_bool_or_schema()
    {
        $this->offsetSet('additionalItems', (object) []);
        $this->offsetGet('additionalItems')->shouldBeObject();

        $this->offsetSet('additionalItems', true);
        $this->offsetGet('additionalItems')->shouldBe(true);

        $this->offsetSet('additionalItems', 0);
        $this->offsetGet('additionalItems')->shouldBe(false);
    }

    function it_should_validate_the_schema_of_additionalItems_if_object_provided()
    {
        // @todo Implement schema validation
    }

    function it_should_throw_exception_if_items_are_not_an_object_or_array()
    {
        $exception = new InvalidTypeException('"items" must be an object or array, you provided a string');
        $this->shouldThrow($exception)->duringOffsetSet('items', 'foo');

        $this->shouldThrow(self::TYPE_EXCEPTION)->duringOffsetSet('items', true);
    }

    function it_should_throw_exception_when_setting_maxItems_without_natural_number_or_zero()
    {
        $this->testNonNumericTypeThrowsException('maxItems');
        $this->testNumberIsGreaterThan('maxItems', -1);
    }

    function it_should_throw_exception_when_setting_minItems_without_natural_number_or_zero()
    {
        $this->testNonNumericTypeThrowsException('minItems');
        $this->testNumberIsGreaterThan('minItems', -1);
    }

    function it_should_support_uniqueItems_only_as_boolean()
    {
        $this->offsetSet('uniqueItems', true);
        $this->offsetGet('uniqueItems')->shouldBe(true);

        $this->offsetSet('uniqueItems', []);
        $this->offsetGet('uniqueItems')->shouldBe(false);
    }

    function it_should_throw_exception_when_setting_maxProperties_without_natural_number_or_zero()
    {
        $this->testNonNumericTypeThrowsException('maxProperties');
        $this->testNumberIsGreaterThan('maxProperties', -1);
    }

    function it_should_throw_exception_when_setting_minProperties_without_natural_number_or_zero()
    {
        $this->testNonNumericTypeThrowsException('minProperties');
        $this->testNumberIsGreaterThan('minProperties', -1);
    }

    function it_should_support_required_keyword_as_array_only()
    {
        $value = new \stdClass();
        $exception = InvalidTypeException::factory('required', $value, 'array');
        $this->shouldThrow($exception)->duringOffsetSet('required', $value);
    }

    function it_should_force_required_array_vals_to_be_strings_and_unique()
    {
        $stream = fopen('php://temp', 'r+');
        $invalid = [
            'foo', 'bar', [], $stream, 'foo', 'baz'
        ];

        $this->shouldThrow('InvalidArgumentException')->duringOffsetSet('required', $invalid);
        fclose($stream);

        $valid = ['foo', 'bar', 'baz', 'bar'];
        $this->offsetSet('required', $valid);
        $this->offsetGet('required')->shouldReturn(['foo', 'bar', 'baz']);
    }

    function it_should_only_let_additionalProperties_be_a_bool_or_schema()
    {
        $this->offsetSet('additionalProperties', (object) []);
        $this->offsetGet('additionalProperties')->shouldBeObject();

        $this->offsetSet('additionalProperties', true);
        $this->offsetGet('additionalProperties')->shouldBe(true);

        $this->offsetSet('additionalProperties', 0);
        $this->offsetGet('additionalProperties')->shouldBe(false);
    }

    function it_should_validate_the_schema_of_additionalProperties_if_object_provided()
    {
        // @todo Implement schema validation
    }

    function it_should_throw_exception_if_properties_are_not_an_object_or_array()
    {
        $exception = new InvalidTypeException('"properties" must be an object or array, you provided a string');
        $this->shouldThrow($exception)->duringOffsetSet('properties', 'foo');

        $this->shouldThrow(self::TYPE_EXCEPTION)->duringOffsetSet('properties', true);
    }

    function it_should_throw_exception_if_dependencies_is_not_an_object()
    {
        $this->shouldThrow(self::TYPE_EXCEPTION)->duringOffsetSet('dependencies', []);
    }

    function it_should_only_let_dependencies_be_an_object_whose_values_are_objects_or_arrays()
    {
        $exception = new \InvalidArgumentException(sprintf(
            "\"dependencies\" should be an object whose values are either "
            . "objects or arrays. One of the values you provided was a boolean"
        ));

        $value = (object) [true];
        $this->shouldThrow($exception)->duringOffsetSet('dependencies', $value);
    }

    function it_should_throw_exception_if_object_in_dependencies_object_is_invalid_schema()
    {
        // @todo Implement schema validation
    }

    function it_should_throw_exception_if_array_in_dependencies_object_is_invalid()
    {
        $value = (object) [
            [
                'foo', 'bar', [], 'baz', new \stdClass()
            ]
        ];

        $exception = new InvalidArgumentException(
            'The array specified is invalid. It must contain a list of unique '
                . 'strings. You provided these erroneous types: array, object'
        );

        $this->shouldThrow($exception)->duringOffsetSet('dependencies', $value);
    }

    function it_should_throw_exception_if_enum_is_not_array()
    {
        $this->shouldThrow('InvalidArgumentException')->duringOffsetSet('enum', 'foo');
    }

    function it_should_allow_enum_any_values_within_its_array()
    {
        $array = ['foo', [], new \stdClass(), 10];
        $this->offsetSet('enum', $array);
        $this->offsetGet('enum')->shouldReturn($array);
    }

    function it_should_support_type_as_either_a_string_or_array()
    {
        $value = ['foo', 'bar', 'baz'];
        $this->offsetSet('type', $value);
        $this->offsetGet('type')->shouldReturn($value);

        $value = 'foo';
        $this->offsetSet('type', $value);
        $this->offsetGet('type')->shouldReturn($value);
    }

    function it_should_throw_exception_when_type_is_not_string_or_array()
    {
        $this->shouldThrow(self::TYPE_EXCEPTION)->duringOffsetSet('type', 1);
        $this->shouldThrow(self::TYPE_EXCEPTION)->duringOffsetSet('type', new \stdClass());
    }

    function it_should_throw_exception_when_anyOf_is_not_array()
    {
        $this->shouldThrow(self::TYPE_EXCEPTION)->duringOffsetSet('anyOf', new \stdClass());
    }

    function it_should_throw_exception_when_oneOf_is_not_array()
    {
        $this->shouldThrow(self::TYPE_EXCEPTION)->duringOffsetSet('oneOf', new \stdClass());
    }

    function it_should_throw_exception_when_not_is_not_valid_schema()
    {
        // @todo Implement schema validation
    }

    function it_should_throw_exception_when_definitions_is_not_an_object()
    {
        $this->shouldThrow(self::TYPE_EXCEPTION)->duringOffsetSet('definitions', []);
    }

    function it_should_throw_exception_when_definitions_object_does_not_contain_valid_schema()
    {
        // @todo Implement schema validation
    }

    function it_should_throw_exception_if_format_not_an_approved_string()
    {
        $this->shouldThrow(self::TYPE_EXCEPTION)->duringOffsetSet('format', []);
        $this->shouldThrow(self::TYPE_EXCEPTION)->duringOffsetSet('format', 'foo');
    }
}

class TestableAbstractSchema extends AbstractSchema
{
}