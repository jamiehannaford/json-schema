<?php

namespace spec\JsonSchema\Schema;

use JsonSchema\Exception\InvalidTypeException;
use JsonSchema\Schema\AbstractSchema;
use JsonSchema\Validator\SchemaValidator;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use InvalidArgumentException;
use spec\JsonSchema\TestHelper;

class AbstractSchemaSpec extends ObjectBehavior
{
    const RANDOM_STRING = 'Foo';
    const TYPE_EXCEPTION = 'JsonSchema\Exception\InvalidTypeException';

    public static function getWrongDataTypes($correctType)
    {
        $allTypes = [
            'string'   => 'foo',
            'int'      => 1,
            'float'    => 2.5,
            'bool'     => true,
            'object'   => new \stdClass(),
            'array'    => [],
            'resource' => fopen('php://temp', 'r+')
        ];

        if ($correctType == 'numeric') {
            $correctType = ['int', 'float'];
        }

        $correctTypes = array_flip((array) $correctType);

        return array_diff_key($allTypes, $correctTypes);
    }

    /**
     * Checks a keyword type validity
     *
     * @param $keyword
     * @param $correctType
     */
    private function testDataType($keyword, $correctType)
    {
        $incorrectTypes = self::getWrongDataTypes($correctType);
        $correctTypes   = array_flip((array) $correctType);

        foreach ($incorrectTypes as $value) {
            $exception = InvalidTypeException::factory($keyword, $value, $correctTypes);
            $this->shouldThrow($exception)->duringValidateKeyword($keyword, $value);
        }

        fclose($incorrectTypes['resource']);
    }

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

    function let(SchemaValidator $validator)
    {
        $this->beAnInstanceOf('spec\JsonSchema\Schema\TestableAbstractSchema');
        $this->beConstructedWith($validator);
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

    function it_should_throw_exception_for_non_string_title()
    {
        $this->testDataType('description', 'string');
    }

    function it_should_have_a_mutable_desc_in_string_form()
    {
        $this->offsetSet('description', self::RANDOM_STRING);
        $this->offsetGet('description')->shouldReturn(self::RANDOM_STRING);

        $this->offsetSet('description', null);
        $this->offsetGet('description')->shouldBeString();
    }

    function it_should_throw_exception_for_non_string_desc()
    {
        $this->testDataType('description', 'string');
    }

    function it_should_support_multipleOf_keyword()
    {
        $this->offsetSet('multipleOf', 50);
        $this->offsetGet('multipleOf')->shouldReturn(50);
    }

    function it_should_validate_multipleOf_keyword_as_natural_number()
    {
        $this->testDataType('multipleOf', 'numeric');
        $this->testNumberIsGreaterThan('multipleOf');
    }

    function it_should_validate_maximum_keyword_as_numeric_type()
    {
        $this->testDataType('maximum', 'numeric');
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
        $this->testDataType('minimum', 'numeric');
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
        $this->testDataType('minLength', 'numeric');
        $this->testNumberIsGreaterThan('minLength', -1);
    }

    function it_should_throw_exception_when_setting_maxLength_without_natural_number_or_zero()
    {
        $this->testDataType('maxLength', 'numeric');
        $this->testNumberIsGreaterThan('maxLength', -1);
    }

    function it_should_throw_exception_if_casting_pattern_to_string_is_impossible()
    {
        $this->testDataType('pattern', 'string');
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
        $this->testDataType('items', ['object', 'array']);
    }

    function it_should_throw_exception_when_setting_maxItems_without_natural_number_or_zero()
    {
        $this->testDataType('maxItems', 'numeric');
        $this->testNumberIsGreaterThan('maxItems', -1);
    }

    function it_should_throw_exception_when_setting_minItems_without_natural_number_or_zero()
    {
        $this->testDataType('minItems', 'numeric');
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
        $this->testDataType('maxProperties', 'numeric');
        $this->testNumberIsGreaterThan('maxProperties', -1);
    }

    function it_should_throw_exception_when_setting_minProperties_without_natural_number_or_zero()
    {
        $this->testDataType('minProperties', 'numeric');
        $this->testNumberIsGreaterThan('minProperties', -1);
    }

    function it_should_support_required_keyword_as_array_only()
    {
        $this->testDataType('required', 'array');
    }

    function it_should_force_required_array_vals_to_be_strings_and_unique()
    {
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
        $this->testDataType('properties', ['object', 'array']);
    }

    function it_should_throw_exception_if_dependencies_is_not_an_object()
    {
        $this->testDataType('dependencies', 'object');
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
        $this->testDataType('enum', 'array');
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
        $this->testDataType('type', ['string', 'array']);
    }

    function it_should_throw_exception_when_anyOf_is_not_array()
    {
        $this->testDataType('anyOf', 'array');
    }

    function it_should_throw_exception_when_oneOf_is_not_array()
    {
        $this->testDataType('oneOf', 'array');
    }

    function it_should_throw_exception_when_not_is_not_valid_schema()
    {
        // @todo Implement schema validation
    }

    function it_should_throw_exception_when_definitions_is_not_an_object()
    {
        $this->testDataType('definitions', 'objects');
    }

    function it_should_throw_exception_when_definitions_object_does_not_contain_valid_schema()
    {
        // @todo Implement schema validation
    }

    function it_should_throw_exception_if_format_not_an_approved_string()
    {
        $this->testDataType('format', 'objects');
        $this->shouldThrow(self::TYPE_EXCEPTION)->duringOffsetSet('format', 'foo');
    }

    function it_should_not_pass_schemas_that_are_not_objects()
    {
        foreach (['foo', 12345, []] as $value) {
            $this->shouldNotBeValidSchema($value);
            $this->shouldThrow(self::TYPE_EXCEPTION)->duringValidateSchema($value);
        }
    }

    function it_should_not_pass_schemas_that_have_incorrect_keywords()
    {
        $wrongSchema = (object) [
            'maxItems' => 'four',
            'enum'     => ['foo'],
            'required' => ['foo']
        ];
        $this->shouldNotBeValidSchema($wrongSchema);

        $wrongSchema = (object) [
            'multipleOf' => 0,
            'enum'       => [40, 60, 80],
            'type'       => 'integer'
        ];
        $this->shouldNotBeValidSchema($wrongSchema);

        $wrongSchema = (object) [
            'required' => false,
            'minItems' => 4
        ];
        $this->shouldNotBeValidSchema($wrongSchema);
    }
}

class TestableAbstractSchema extends AbstractSchema
{
}