<?php

namespace spec\JsonSchema\Schema;

use JsonSchema\Enum\StrictnessMode;
use JsonSchema\Schema\AbstractSchema;
use JsonSchema\Validator\Constraint\ArrayConstraint;
use JsonSchema\Validator\Constraint\BooleanConstraint;
use JsonSchema\Validator\Constraint\ConstraintInterface;
use JsonSchema\Validator\Constraint\NumberConstraint;
use JsonSchema\Validator\Constraint\ObjectConstraint;
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
        $validator->validate()->shouldBeCalled();

        // Fake a "FALSE" validation
        $this->offsetSet($name, $val);
        $validator->validate()->willReturn(false);

        // Setting fails
        $this->offsetSet($name, $val);
        $this->offsetGet($name)->shouldReturn(null);
    }

    function it_should_validate_title_as_string(SchemaValidator $validator, StringConstraint $constraint)
    {
        $this->testValidationPrediction('title', $validator, $constraint);
    }

    function it_should_validate_desc_as_string(SchemaValidator $validator, StringConstraint $constraint)
    {
        $this->testValidationPrediction('description', $validator, $constraint);
    }

    function it_should_validate_multipleOf_as_number_greater_than_0(SchemaValidator $validator, NumberConstraint $constraint)
    {
        $this->testValidationPrediction('multipleOf', $validator, $constraint);
    }

    function it_should_validate_maximum_as_number(SchemaValidator $validator, NumberConstraint $constraint)
    {
        $this->testValidationPrediction('maximum', $validator, $constraint);
    }

    function it_should_validate_exclusiveMaximum_as_boolean(SchemaValidator $validator, BooleanConstraint $constraint)
    {
        $this->testValidationPrediction('exclusiveMaximum', $validator, $constraint);
    }

    function it_should_validate_minimum_as_number(SchemaValidator $validator, NumberConstraint $constraint)
    {
        $this->testValidationPrediction('minimum', $validator, $constraint);
    }

    function it_should_validate_exclusiveMinimum_as_boolean(SchemaValidator $validator, BooleanConstraint $constraint)
    {
        $this->testValidationPrediction('exclusiveMinimum', $validator, $constraint);
    }

    function it_should_validate_maxLength_as_number(SchemaValidator $validator, NumberConstraint $constraint)
    {
        $constraint->setLowerBound(Argument::type('int'))->shouldBeCalled();
        $constraint->setExclusive(Argument::type('bool'))->shouldBeCalled();

        $this->testValidationPrediction('maxLength', $validator, $constraint);
    }

    function it_should_validate_minLength_as_number(SchemaValidator $validator, NumberConstraint $constraint)
    {
        $constraint->setLowerBound(Argument::type('int'))->shouldBeCalled();
        $constraint->setExclusive(Argument::type('bool'))->shouldBeCalled();

        $this->testValidationPrediction('minLength', $validator, $constraint);
    }

    function it_should_validate_pattern_as_regex_string(SchemaValidator $validator, StringConstraint $constraint)
    {
        $constraint->setRegexValidation(Argument::type('bool'))->shouldBeCalled();

        $this->testValidationPrediction('pattern', $validator, $constraint);
    }

    function it_should_validate_additionalItems_as_boolean(
        SchemaValidator $validator,
        BooleanConstraint $booleanConstraint,
        ObjectConstraint $objectConstraint
    ) {
        // Make sure the validator knows either constraint is acceptable
        $validator->setStrictnessMode(StrictnessMode::ANY)->shouldBeCalled();

        // Objects need to be valid schemas
        $objectConstraint->setSchemaValidation(Argument::type('bool'))->shouldBeCalled();

        $value = 'foo';
        $name = 'additionalItems';

        // Make sure the method is stubbed
        $validator->createConstraint('BooleanConstraint', Argument::any())->willReturn($booleanConstraint);
        $validator->createConstraint('ObjectConstraint', Argument::any())->willReturn($objectConstraint);

        // Now initiate validator name search
        $this->getKeywordConstraints($name, $value);

        // Make sure our expectation has been fulfilled
        $validator->createConstraint('BooleanConstraint', $value)->shouldHaveBeenCalled();
        $validator->createConstraint('ObjectConstraint', $value)->shouldHaveBeenCalled();
    }

    function it_should_validate_items_as_either_object_or_array(
        SchemaValidator $validator,
        ObjectConstraint $objectConstraint,
        ArrayConstraint $arrayConstraint
    ) {
        // Make sure the validator knows either constraint is acceptable
        $validator->setStrictnessMode(StrictnessMode::ANY)->shouldBeCalled();

        // Objects need to be valid schemas
        $objectConstraint->setSchemaValidation(Argument::type('bool'))->shouldBeCalled();

        // Array items need to be valid schemas
        $arrayConstraint->setNestedSchemaValidation(Argument::type('bool'))->shouldBeCalled();

        $value = 'foo';
        $name = 'items';

        // Make sure the method is stubbed
        $validator->createConstraint('ArrayConstraint', Argument::any())->willReturn($arrayConstraint);
        $validator->createConstraint('ObjectConstraint', Argument::any())->willReturn($objectConstraint);

        // Now initiate validator name search
        $this->getKeywordConstraints($name, $value);

        // Make sure our expectation has been fulfilled
        $validator->createConstraint('ArrayConstraint', $value)->shouldHaveBeenCalled();
        $validator->createConstraint('ObjectConstraint', $value)->shouldHaveBeenCalled();
    }

    function it_should_validate_maxItems_as_number_greater_than_or_equal_to_0(
        SchemaValidator $validator, NumberConstraint $constraint
    ) {
        $constraint->setLowerBound(0)->shouldBeCalled();
        $constraint->setExclusive(false)->shouldBeCalled();

        $this->testValidationPrediction('maxItems', $validator, $constraint);
    }

    function it_should_validate_minItems_as_number_greater_than_or_equal_to_0(
        SchemaValidator $validator, NumberConstraint $constraint
    ) {
        $constraint->setLowerBound(0)->shouldBeCalled();
        $constraint->setExclusive(false)->shouldBeCalled();

        $this->testValidationPrediction('minItems', $validator, $constraint);
    }

    function it_should_validate_uniqueItems_as_bool(SchemaValidator $validator, BooleanConstraint $constraint)
    {
        $this->testValidationPrediction('uniqueItems', $validator, $constraint);
    }

    function it_should_validate_maxProperties_as_number_greater_than_or_equal_to_0(
        SchemaValidator $validator, NumberConstraint $constraint
    ) {
        $constraint->setLowerBound(0)->shouldBeCalled();
        $constraint->setExclusive(false)->shouldBeCalled();

        $this->testValidationPrediction('maxProperties', $validator, $constraint);
    }

    function it_should_validate_minProperties_as_number_greater_than_or_equal_to_0(
        SchemaValidator $validator, NumberConstraint $constraint
    ) {
        $constraint->setLowerBound(0)->shouldBeCalled();
        $constraint->setExclusive(false)->shouldBeCalled();

        $this->testValidationPrediction('minProperties', $validator, $constraint);
    }

    function it_should_validate_required_as_array(SchemaValidator $validator, ArrayConstraint $constraint)
    {
        $constraint->setInternalType('string')->shouldBeCalled();
        $constraint->setUniqueness(true)->shouldBeCalled();
        $constraint->setMinimumCount(1)->shouldBeCalled();
        $this->testValidationPrediction('required', $validator, $constraint);
    }

    function it_should_validate_additionalProperties_as_either_bool_or_object()
    {
        
    }

    function it_should_consider_additionalProperties_a_json_schema_if_object()
    {

    }

    function it_should_validate_properties_as_object()
    {

    }

    function it_should_consider_properties_an_object_whose_properties_are_json_schemas()
    {

    }

    function it_should_validate_patternProperties_as_object()
    {

    }

    function it_should_insist_on_patternProperties_having_regex_strings_for_object_properties()
    {

    }

    function it_should_insist_on_patternProperties_having_json_schemas_for_object_vals()
    {

    }

    function it_should_validate_dependencies_as_object()
    {

    }

    function it_should_insist_on_dependencies_having_either_arrays_or_objects_as_object_properties()
    {

    }

    function it_should_ensure_that_values_of_dependencies_which_are_values_are_json_schemas()
    {

    }

    function it_should_ensure_that_values_of_dependencies_which_are_arrays_contain_unique_strings()
    {

    }

    /*** HELPERS ***/

    private function testValidationPrediction($name, ValidatorInterface $validator, ConstraintInterface $constraint)
    {
        // Value is arbitrary
        $value = 'foo';

        // Make sure the method is stubbed
        $validator->createConstraint(Argument::any(), Argument::any())->willReturn($constraint);

        // Now initiate validator name search
        $this->getKeywordConstraints($name, $value);

        // Make sure our expectation has been fulfilled
        $validator->createConstraint($this->getCollaboratorName($constraint), $value)->shouldHaveBeenCalled();
    }

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