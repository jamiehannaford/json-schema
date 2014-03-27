<?php

namespace spec\JsonSchema\Validator\Constraint;

use JsonSchema\Schema\RootSchema;
use JsonSchema\Validator\ErrorHandler\BufferErrorHandler;
use JsonSchema\Validator\SchemaValidator;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Prophecy\Promise\ReturnPromise;
use Symfony\Component\EventDispatcher\EventDispatcher;

class ArrayConstraintSpec extends ObjectBehavior
{
    function let(EventDispatcher $dispatcher)
    {
        $this->beConstructedWith([], $dispatcher);
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

    function it_should_fail_validation_if_invalid_schemas_found_when_option_set(SchemaValidator $validator, RootSchema $schema)
    {
        $value = ['foo' => (object) [
            'enum'  => 'invalid_enum',
            'title' => 1356
        ]];

        $this->setValue($value);
        $this->setNestedSchemaValidation(true);

        $schema->setValidator($validator);
        $schema->setData($value);
        $schema->isValid()->willReturn(false);

        $this->validate()->shouldReturn(false);
    }

    function it_should_fail_validation_if_internal_type_does_not_match_option()
    {
        $this->setInternalType('boolean');
        $this->setValue(['foo', true, false]);
        $this->validate()->shouldReturn(false);
    }

    function it_should_force_uniqueness_if_option_set()
    {
        $this->setUniqueness(true);
        $this->setValue(['foo', 'bar', 'foo']);
        $this->validate();
        $this->getValue()->shouldReturn(['foo', 'bar']);
    }

    function it_should_fail_if_count_is_less_than_min_count()
    {
        $value = array_fill(0, 9, 'foo');

        $this->setMinimumCount(10);
        $this->setValue($value);

        $this->validate()->shouldReturn(false);
    }
}