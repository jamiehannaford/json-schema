<?php

namespace spec\JsonSchema\Schema;

use JsonSchema\Schema\AbstractSchema;
use JsonSchema\Validator\Constraint\NumberConstraint;
use JsonSchema\Validator\Constraint\StringConstraint;
use JsonSchema\Validator\SchemaValidator;
use PhpSpec\ObjectBehavior;
use PhpSpec\Wrapper\Collaborator;
use Prophecy\Argument;
use Prophecy\Prophet;

class AbstractSchemaSpec extends ObjectBehavior
{
    const RANDOM_STRING = 'Foo';
    const TYPE_EXCEPTION = 'JsonSchema\Exception\InvalidTypeException';

    private $prophet;

    function let(SchemaValidator $validator)
    {
        $this->prophet = new Prophet();

        $this->beAnInstanceOf('spec\JsonSchema\Schema\TestableAbstractSchema');
        $this->beConstructedWith($validator);
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

    function it_should_set_title_if_validation_passes(SchemaValidator $validator, StringConstraint $constraint)
    {
        $this->testKeywordMutability('title', 'Foo', $validator, $constraint);
    }

    function it_should_not_set_title_if_validation_fails(SchemaValidator $validator, StringConstraint $constraint)
    {
        $this->testKeywordValidationGenericFailure('title', $validator, $constraint);
    }

    function it_should_set_desc_if_validation_passes(SchemaValidator $validator, StringConstraint $constraint)
    {
        $this->testKeywordMutability('description', 'Foo', $validator, $constraint);
    }

    function it_should_not_set_desc_if_validation_fails(SchemaValidator $validator, StringConstraint $constraint)
    {
        $this->testKeywordValidationGenericFailure('description', $validator, $constraint);
    }

    function it_should_set_multipleOf_if_validation_passes(SchemaValidator $validator, NumberConstraint $constraint)
    {
        $this->testKeywordMutability('multipleOf', 100, $validator, $constraint);
    }

    function it_should_not_set_multipleOf_if_validation_fails(SchemaValidator $validator, NumberConstraint $constraint)
    {
        $this->testKeywordValidationGenericFailure('multipleOf', $validator, $constraint);
    }

    /*** HELPERS ***/

    public function testMutability($key, $val)
    {
        $this->offsetSet($key, $val);
        $this->offsetGet($key)->shouldReturn($val);
    }

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

        // Add to validator
        $validator->addConstraint($constraint)->shouldBeCalled();

        // Reset validator
        $this->setValidator($validator);
    }

    private function testKeywordMutability($name, $val, $validator, $constraint)
    {
        $this->setupKeywordCollaborators($val, $validator, $constraint);

        // Fake a "TRUE" validation
        $validator->validate()->willReturn(true);

        // Make sure basic setter/getter logic works
        $this->testMutability($name, $val);
    }

    private function testKeywordValidationGenericFailure($name, SchemaValidator $validator, StringConstraint $constraint)
    {
        $val = 'Foo';
        $this->setupKeywordCollaborators($val, $validator, $constraint);

        // Fake a "FALSE" validation
        $validator->validate()->willReturn(false);

        // Setting fails
        $this->offsetSet($name, $val);
        $this->offsetGet($name)->shouldReturn(null);
    }
}

class TestableAbstractSchema extends AbstractSchema
{
}