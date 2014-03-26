<?php

namespace spec\JsonSchema\Schema;

use JsonSchema\Schema\AbstractSchema;
use JsonSchema\Validator\Constraint\BooleanConstraint;
use JsonSchema\Validator\Constraint\ConstraintInterface;
use JsonSchema\Validator\Constraint\NumberConstraint;
use JsonSchema\Validator\Constraint\StringConstraint;
use JsonSchema\Validator\SchemaValidator;
use JsonSchema\Validator\ValidatorInterface;
use PhpSpec\ObjectBehavior;
use PhpSpec\Wrapper\Collaborator;
use Prophecy\Argument;

class AbstractSchemaSpec extends ObjectBehavior
{
    const RANDOM_STRING  = 'Foo';
    const TYPE_EXCEPTION = 'JsonSchema\Exception\InvalidTypeException';

    function let(SchemaValidator $validator, StringConstraint $constraint)
    {
        $data = (object) ['title' => 'foo'];

        // Define collaborator promises
        $validator->createConstraint('StringConstraint', 'foo')->willReturn($constraint);
        $validator->addConstraint($constraint)->shouldBeCalled();
        $validator->validate()->shouldBeCalled();

        // Basic object setup
        $this->beAnInstanceOf('spec\JsonSchema\Schema\TestableAbstractSchema');
        $this->beConstructedWith($validator, $data);
    }

    function it_should_throw_exception_if_schema_data_is_not_object()
    {
        $this->shouldThrow('InvalidArgumentException')->duringSetData([]);
    }

    function it_should_populate_keywords_when_correct(SchemaValidator $validator, StringConstraint $constraint)
    {
        $validator->createConstraint('StringConstraint', 'Foo bar')->willReturn($constraint);
        $validator->addConstraint($constraint)->shouldBeCalled();
        $validator->validate()->willReturn(true);
        $validator->validate()->shouldBeCalled();

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

        // Fake a "FALSE" validation
        $this->offsetSet($name, $val);
        $validator->validate()->willReturn(false);

        // Setting fails
        $this->offsetSet($name, $val);
        $this->offsetGet($name)->shouldReturn(null);
    }

    function it_should_validate_title_as_string(SchemaValidator $validator, StringConstraint $constraint)
    {
        $this->testValidationPrediction('title', 'StringConstraint', $validator, $constraint);
    }

    function it_should_validate_desc_as_string(SchemaValidator $validator, StringConstraint $constraint)
    {
        $this->testValidationPrediction('description', 'StringConstraint', $validator, $constraint);
    }

    /*** HELPERS ***/

    private function testValidationPrediction($name, $constraintName, ValidatorInterface $validator, ConstraintInterface $constraint)
    {
        // Value is arbitrary
        $value = 'foo';

        // Make sure the method is stubbed
        $validator->createConstraint(Argument::any(), Argument::any())->willReturn($constraint);

        // Set up explicit expectation
        $validator->createConstraint($constraintName, $value)->shouldBeCalled();

        // Now initiate validator name search
        $this->getKeywordConstraints($name, $value);
    }

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
}

class TestableAbstractSchema extends AbstractSchema
{
}