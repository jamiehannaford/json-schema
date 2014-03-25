<?php

namespace spec\JsonSchema\Schema;

use JsonSchema\Schema\AbstractSchema;
use JsonSchema\Validator\Constraint\BooleanConstraint;
use JsonSchema\Validator\Constraint\ConstraintInterface;
use JsonSchema\Validator\Constraint\NumberConstraint;
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

//    function it_should_populate_keywords_when_correct(SchemaValidator $validator, StringConstraint $constraint)
//    {
//        $validator->createConstraint('StringConstraint', 'Foo bar')->willReturn($constraint);
//        $validator->addConstraint($constraint)->shouldBeCalled();
//        $validator->validate()->willReturn(true);
//        $validator->validate()->shouldBeCalled();
//
//        $data = (object) ['description' => 'Foo bar'];
//        $this->setData($data);
//        $this->offsetGet('description')->shouldReturn('Foo bar');
//    }
//
//    function it_should_have_validator(SchemaValidator $validator)
//    {
//        $this->setValidator($validator);
//        $this->getValidator()->shouldReturn($validator);
//    }
//
//    function it_should_recognize_keywords()
//    {
//        $this->shouldBeKeyword('title');
//        $this->shouldBeKeyword('multipleOf');
//
//        $this->shouldNotBeKeyword('Foo');
//        $this->shouldNotBeKeyword(['Bar']);
//    }
//
//    function it_can_set_non_keywords()
//    {
//        $this->testMutability('Foo', 'Bar');
//    }
//
//    function it_should_set_title_if_validation_passes(SchemaValidator $validator, StringConstraint $constraint)
//    {
//        $this->testKeywordMutability('title', 'Foo', $validator, $constraint);
//    }
//
//    function it_should_not_set_title_if_validation_fails(SchemaValidator $validator, StringConstraint $constraint)
//    {
//        $this->testKeywordValidationGenericFailure('title', $validator, $constraint);
//    }
//
//    function it_should_set_desc_if_validation_passes(SchemaValidator $validator, StringConstraint $constraint)
//    {
//        $this->testKeywordMutability('description', 'Foo', $validator, $constraint);
//    }
//
//    function it_should_not_set_desc_if_validation_fails(SchemaValidator $validator, StringConstraint $constraint)
//    {
//        $this->testKeywordValidationGenericFailure('description', $validator, $constraint);
//    }
//
//    function it_should_set_multipleOf_if_validation_passes(SchemaValidator $validator, NumberConstraint $constraint)
//    {
//        $this->testKeywordMutability('multipleOf', 100, $validator, $constraint);
//    }
//
//    function it_should_not_set_multipleOf_if_validation_fails(SchemaValidator $validator, NumberConstraint $constraint)
//    {
//        $this->testKeywordValidationGenericFailure('multipleOf', $validator, $constraint);
//    }
//
//    function it_should_set_maximum_if_validation_passes(SchemaValidator $validator, NumberConstraint $constraint)
//    {
//        $this->testKeywordMutability('maximum', 100, $validator, $constraint);
//    }
//
//    function it_should_not_set_maximum_if_validation_fails(SchemaValidator $validator, NumberConstraint $constraint)
//    {
//        $this->testKeywordValidationGenericFailure('maximum', $validator, $constraint);
//    }

    function it_should_set_exclusiveMaximum_if_validation_passes(SchemaValidator $validator, BooleanConstraint $constraint)
    {
        // Create constraint object using factory
        $validator->createConstraint('BooleanConstraint', true)->willReturn($constraint);
        // Add to validator
        $validator->addConstraint($constraint)->shouldBeCalled();
        // Reset validator
        $this->setValidator($validator);
        $validator->validate()->willReturn(true);

        $this->offsetSet('exclusiveMaximum', true);
        $this->offsetGet('exclusiveMaximum')->shouldReturn(true);

        // Create constraint object using factory
        $validator->createConstraint('BooleanConstraint', 'Foo')->willReturn($constraint);
        // Add to validator
        $validator->addConstraint($constraint)->shouldBeCalled();
        // Reset validator
        $this->setValidator($validator);
        $validator->validate()->willReturn(false);

        $this->offsetSet('exclusiveMaximum', 'Foo');
        $this->offsetGet('exclusiveMaximum')->shouldReturn(null);
    }

    function it_should_set_minimum_if_validation_passes(SchemaValidator $validator, StringConstraint $constraint)
    {
    }

    function it_should_not_set_minimum_if_validation_fails(SchemaValidator $validator, StringConstraint $constraint)
    {

    }

    function it_should_set_exclusiveMinimum_if_validation_passes(SchemaValidator $validator, StringConstraint $constraint)
    {

    }

    function it_should_not_set_exclusiveMinimum_if_validation_fails(SchemaValidator $validator, StringConstraint $constraint)
    {

    }

    function it_should_set_maxLength_if_validation_passes(SchemaValidator $validator, StringConstraint $constraint)
    {

    }

    function it_should_not_set_maxLength_if_validation_fails(SchemaValidator $validator, StringConstraint $constraint)
    {

    }

    function it_should_set_minLength_if_validation_passes(SchemaValidator $validator, StringConstraint $constraint)
    {

    }

    function it_should_not_set_minLength_if_validation_fails(SchemaValidator $validator, StringConstraint $constraint)
    {

    }

    function it_should_set_pattern_if_validation_passes(SchemaValidator $validator, StringConstraint $constraint)
    {

    }

    function it_should_not_set_pattern_if_validation_fails(SchemaValidator $validator, StringConstraint $constraint)
    {

    }

    function it_should_set_additionalItems_if_validation_passes(SchemaValidator $validator, StringConstraint $constraint)
    {

    }

    function it_should_not_set_additionalItems_if_validation_fails(SchemaValidator $validator, StringConstraint $constraint)
    {

    }

    function it_should_set_items_if_validation_passes(SchemaValidator $validator, StringConstraint $constraint)
    {

    }

    function it_should_not_set_items_if_validation_fails(SchemaValidator $validator, StringConstraint $constraint)
    {

    }

    function it_should_set_maxItems_if_validation_passes(SchemaValidator $validator, StringConstraint $constraint)
    {

    }

    function it_should_not_set_maxItems_if_validation_fails(SchemaValidator $validator, StringConstraint $constraint)
    {

    }

    function it_should_set_minItems_if_validation_passes(SchemaValidator $validator, StringConstraint $constraint)
    {

    }

    function it_should_not_set_minItems_if_validation_fails(SchemaValidator $validator, StringConstraint $constraint)
    {

    }

    function it_should_set_uniqueItems_if_validation_passes(SchemaValidator $validator, StringConstraint $constraint)
    {

    }

    function it_should_not_set_uniqueItems_if_validation_fails(SchemaValidator $validator, StringConstraint $constraint)
    {

    }

    function it_should_set_maxProperties_if_validation_passes(SchemaValidator $validator, StringConstraint $constraint)
    {

    }

    function it_should_not_set_maxProperties_if_validation_fails(SchemaValidator $validator, StringConstraint $constraint)
    {

    }

    function it_should_set_minProperties_if_validation_passes(SchemaValidator $validator, StringConstraint $constraint)
    {

    }

    function it_should_not_set_minProperties_if_validation_fails(SchemaValidator $validator, StringConstraint $constraint)
    {

    }

    function it_should_set_required_if_validation_passes(SchemaValidator $validator, StringConstraint $constraint)
    {

    }

    function it_should_not_set_required_if_validation_fails(SchemaValidator $validator, StringConstraint $constraint)
    {

    }

    function it_should_set_additionalProperties_if_validation_passes(SchemaValidator $validator, StringConstraint $constraint)
    {

    }

    function it_should_not_set_additionalProperties_if_validation_fails(SchemaValidator $validator, StringConstraint $constraint)
    {

    }

    function it_should_set_properties_if_validation_passes(SchemaValidator $validator, StringConstraint $constraint)
    {

    }

    function it_should_not_set_properties_if_validation_fails(SchemaValidator $validator, StringConstraint $constraint)
    {

    }

    function it_should_set_patternProperties_if_validation_passes(SchemaValidator $validator, StringConstraint $constraint)
    {

    }

    function it_should_not_set_patternProperties_if_validation_fails(SchemaValidator $validator, StringConstraint $constraint)
    {

    }

    function it_should_set_dependencies_if_validation_passes(SchemaValidator $validator, StringConstraint $constraint)
    {

    }

    function it_should_not_set_dependencies_if_validation_fails(SchemaValidator $validator, StringConstraint $constraint)
    {

    }

    function it_should_set_enum_if_validation_passes(SchemaValidator $validator, StringConstraint $constraint)
    {

    }

    function it_should_not_set_enum_if_validation_fails(SchemaValidator $validator, StringConstraint $constraint)
    {

    }

    function it_should_set_type_if_validation_passes(SchemaValidator $validator, StringConstraint $constraint)
    {

    }

    function it_should_not_set_type_if_validation_fails(SchemaValidator $validator, StringConstraint $constraint)
    {

    }

    function it_should_set_allOf_if_validation_passes(SchemaValidator $validator, StringConstraint $constraint)
    {

    }

    function it_should_not_set_allOf_if_validation_fails(SchemaValidator $validator, StringConstraint $constraint)
    {

    }

    function it_should_set_anyOf_if_validation_passes(SchemaValidator $validator, StringConstraint $constraint)
    {

    }

    function it_should_not_set_anyOf_if_validation_fails(SchemaValidator $validator, StringConstraint $constraint)
    {

    }

    function it_should_set_oneOf_if_validation_passes(SchemaValidator $validator, StringConstraint $constraint)
    {

    }

    function it_should_not_set_oneOf_if_validation_fails(SchemaValidator $validator, StringConstraint $constraint)
    {

    }

    function it_should_set_not_if_validation_passes(SchemaValidator $validator, StringConstraint $constraint)
    {

    }

    function it_should_not_set_not_if_validation_fails(SchemaValidator $validator, StringConstraint $constraint)
    {

    }

    function it_should_set_definitions_if_validation_passes(SchemaValidator $validator, StringConstraint $constraint)
    {

    }

    function it_should_not_set_definitions_if_validation_fails(SchemaValidator $validator, StringConstraint $constraint)
    {

    }

    function it_should_set_default_if_validation_passes(SchemaValidator $validator, StringConstraint $constraint)
    {

    }

    function it_should_not_set_default_if_validation_fails(SchemaValidator $validator, StringConstraint $constraint)
    {

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

    private function testKeywordValidationGenericFailure($name, SchemaValidator $validator, ConstraintInterface $constraint)
    {
        $val = 'Foo';
        $this->setupKeywordCollaborators($val, $validator, $constraint);

        // Fake a "FALSE" validation
        $this->offsetSet($name, $val);
        $validator->validate()->willReturn(false);

        // Setting fails
        $this->offsetSet($name, $val);
        $this->offsetGet($name)->shouldReturn(null);
    }
}

class TestableAbstractSchema extends AbstractSchema
{
}