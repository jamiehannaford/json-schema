<?php

namespace spec\JsonSchema\Schema;

use JsonSchema\Schema\AbstractSchema;
use JsonSchema\Validator\Constraint\StringConstraint;
use JsonSchema\Validator\SchemaValidator;
use PhpSpec\ObjectBehavior;
use PhpSpec\Wrapper\Collaborator;
use Prophecy\Argument;

class AbstractSchemaSpec extends ObjectBehavior
{
    const RANDOM_STRING  = 'Foo';
    const TYPE_EXCEPTION = 'JsonSchema\Exception\InvalidTypeException';

    function let(SchemaValidator $validator, StringConstraint $constraint)
    {
        $this->beAnInstanceOf('spec\JsonSchema\Schema\TestableAbstractSchema');
        $this->beConstructedWith($validator, (object) ['foo' => 'bar']);
    }

    function it_should_throw_exception_if_schema_data_is_not_object()
    {
        $this->shouldThrow('InvalidArgumentException')->duringSetData([]);
    }

    function it_should_populate_keywords_when_correct(SchemaValidator $validator, StringConstraint $constraint)
    {
        $validator->createConstraint('StringConstraint', 'Foo bar')->willReturn($constraint);

        $validator->validateKeyword(Argument::any(), Argument::any())->willReturn(true);
        $validator->validateKeyword(Argument::any(), Argument::any())->shouldBeCalled();

        $data = (object) ['description' => 'Foo bar'];
        $this->setData($data);
        $this->offsetGet('description')->shouldReturn('Foo bar');
    }

    function it_should_have_validator(SchemaValidator $validator)
    {
        $this->setValidator($validator);
        $this->getValidator()->shouldReturn($validator);
    }

    function it_should_recognize_keywords()
    {
        $this->shouldBeKeyword('title');
        $this->shouldBeKeyword('multipleOf');

        $this->shouldNotBeKeyword('Foo');
        $this->shouldNotBeKeyword(['Bar']);
    }

    function it_can_set_non_keywords()
    {
        $this->testMutability('Foo', 'Bar');
    }

    function it_should_set_keyword_if_validation_passes(SchemaValidator $validator, StringConstraint $constraint)
    {
        $this->testKeywordMutability('title', 'Foo', $validator, $constraint);
    }

    function it_should_not_set_keyword_if_validation_fails(SchemaValidator $validator, StringConstraint $constraint)
    {
        $val = 101;
        $name = 'title';

        $this->setupKeywordCollaborators($val, $validator, $constraint);
        $validator->validateKeyword(Argument::any(), Argument::any())->shouldBeCalled();

        // Fake a "FALSE" validation
        $this->offsetSet($name, $val);
        $validator->validateKeyword(Argument::any(), Argument::any())->willReturn(false);

        // Setting fails
        $this->offsetSet($name, $val);
        $this->offsetGet($name)->shouldReturn(null);
    }

    function it_should_not_be_valid_if_validator_returns_errors(SchemaValidator $validator)
    {
        $wrongSchemaData = (object) [
            'enum'  => 'foo',
            'title' => 23
        ];

        $validator->validateKeyword(Argument::any(), Argument::any())->shouldBeCalled();

        $validator->getErrorCount()->willReturn(2);
        $this->setValidator($validator);

        $this->setData($wrongSchemaData);

        $this->shouldNotBeValid();
    }

    /*** HELPERS ***/

    public function testMutability($key, $val)
    {
        $this->offsetSet($key, $val);
        $this->offsetGet($key)->shouldReturn($val);
    }

    private function getCollaboratorName(Collaborator $object)
    {
        $reflection = new \ReflectionObject($object->getWrappedObject());
        $namespace = $reflection->getNamespaceName();
        return substr($namespace, strrpos($namespace, '\\') + 1);
    }

    private function setupKeywordCollaborators($val, $validator, $constraint)
    {
        // Create constraint object using factory
        $constraintName = $this->getCollaboratorName($constraint);
        $validator->createConstraint($constraintName, $val)->willReturn($constraint);

        // Reset validator
        $this->setValidator($validator);
    }

    private function testKeywordMutability($name, $val, $validator, $constraint)
    {
        $this->setupKeywordCollaborators($val, $validator, $constraint);

        // Fake a "TRUE" validation
        $validator->validateKeyword($name, $val)->willReturn(true);

        // Make sure basic setter/getter logic works
        $this->testMutability($name, $val);
    }
}

class TestableAbstractSchema extends AbstractSchema
{
}