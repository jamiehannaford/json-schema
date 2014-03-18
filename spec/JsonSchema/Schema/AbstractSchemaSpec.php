<?php

namespace spec\JsonSchema\Schema;

use JsonSchema\Schema\AbstractSchema;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class AbstractSchemaSpec extends ObjectBehavior
{
    const RANDOM_STRING = 'Foo';

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

    function it_should_throw_exception_when_setting_multipleOf_without_natural_number()
    {
        $this->shouldThrow('\InvalidArgumentException')->duringOffsetSet('multipleOf', []);
        $this->shouldThrow('\InvalidArgumentException')->duringOffsetSet('multipleOf', 0);

        $exception = new \InvalidArgumentException('"multipleOf" must be a positive integer greater than 0, you provided -1');
        $this->shouldThrow($exception)->duringOffsetSet('multipleOf', -1);

        $exception = new \InvalidArgumentException('"multipleOf" must be a numeric value, you provided a string');
        $this->shouldThrow($exception)->duringOffsetSet('multipleOf', 'string');
    }

    function it_should_throw_exception_when_setting_maximum_without_numeric_type()
    {
        $exception = new \InvalidArgumentException('"maximum" must be a numeric value, you provided a string');
        $this->shouldThrow($exception)->duringOffsetSet('maximum', 'foo');
    }

    function it_should_support_exclusiveMaximum_keyword()
    {
        $this->offsetSet('exclusiveMaximum', true);
        $this->offsetGet('exclusiveMaximum')->shouldReturn(true);

        $this->offsetSet('exclusiveMaximum', 0);
        $this->offsetGet('exclusiveMaximum')->shouldReturn(false);
    }

    function it_should_throw_exception_when_setting_minimum_without_numeric_type()
    {
        $exception = new \InvalidArgumentException('"minimum" must be a numeric value, you provided a string');
        $this->shouldThrow($exception)->duringOffsetSet('minimum', 'foo');
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
        $this->shouldThrow('\InvalidArgumentException')->duringOffsetSet('minLength', []);

        $exception = new \InvalidArgumentException('"minLength" must be a positive integer greater than -1, you provided -1');
        $this->shouldThrow($exception)->duringOffsetSet('minLength', -1);

        $exception = new \InvalidArgumentException('"minLength" must be a numeric value, you provided a string');
        $this->shouldThrow($exception)->duringOffsetSet('minLength', 'string');
    }

    function it_should_throw_exception_when_setting_maxLength_without_natural_number_or_zero()
    {
        $this->shouldThrow('\InvalidArgumentException')->duringOffsetSet('maxLength', []);

        $exception = new \InvalidArgumentException('"maxLength" must be a positive integer greater than -1, you provided -1');
        $this->shouldThrow($exception)->duringOffsetSet('maxLength', -1);

        $exception = new \InvalidArgumentException('"maxLength" must be a numeric value, you provided a string');
        $this->shouldThrow($exception)->duringOffsetSet('maxLength', 'string');
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

    }

    function it_should_throw_exception_if_items_are_not_an_object_or_array()
    {

    }
}

class TestableAbstractSchema extends AbstractSchema
{
}