<?php

namespace spec\JsonSchema\Validator;

use JsonSchema\Validator\Constraint\ConstraintFactoryInterface;
use JsonSchema\Validator\Constraint\ConstraintInterface;
use PhpSpec\ObjectBehavior;
use PhpSpec\Wrapper\Collaborator;
use Prophecy\Argument;
use Prophecy\Prophet;

class SchemaValidatorSpec extends ObjectBehavior
{
    private $prophet;

    function let()
    {
        $this->prophet = new Prophet();
    }

    function letgo()
    {
        $this->prophet->checkPredictions();
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('JsonSchema\Validator\SchemaValidator');
        $this->shouldImplement('JsonSchema\Validator\ValidatorInterface');
    }

    function it_should_validate_title_as_string()
    {
        $this->testValidationPrediction('title', $this->prophesizeConstraint('StringConstraint'));
    }

    function it_should_validate_desc_as_string()
    {
        $this->testValidationPrediction('description', $this->prophesizeConstraint('StringConstraint'));
    }

    function it_should_validate_multipleOf_as_number_greater_than_0()
    {
        $this->testValidationPrediction('multipleOf', $this->prophesizeConstraint('NumberConstraint'));
    }

    function it_should_validate_maximum_as_number()
    {
        $this->testValidationPrediction('maximum', $this->prophesizeConstraint('NumberConstraint'));
    }

    function it_should_validate_exclusiveMaximum_as_boolean()
    {
        $this->testValidationPrediction('exclusiveMaximum', $this->prophesizeConstraint('BooleanConstraint'));
    }

    function it_should_validate_minimum_as_number()
    {
        $this->testValidationPrediction('minimum', $this->prophesizeConstraint('NumberConstraint'));
    }

    function it_should_validate_exclusiveMinimum_as_boolean()
    {
        $this->testValidationPrediction('exclusiveMinimum', $this->prophesizeConstraint('BooleanConstraint'));
    }

    function it_should_validate_maxLength_as_number()
    {
        $constraint = $this->prophesizeConstraint('NumberConstraint');

        $constraint->setLowerBound(Argument::type('int'))->shouldBeCalled();
        $constraint->setExclusive(Argument::type('bool'))->shouldBeCalled();

        $this->testValidationPrediction('maxLength', $constraint);
    }

    function it_should_validate_minLength_as_number()
    {
        $constraint = $this->prophesizeConstraint('NumberConstraint');

        $constraint->setLowerBound(Argument::type('int'))->shouldBeCalled();
        $constraint->setExclusive(Argument::type('bool'))->shouldBeCalled();

        $this->testValidationPrediction('minLength', $constraint);
    }

    function it_should_validate_pattern_as_regex_string()
    {
        $constraint = $this->prophesizeConstraint('StringConstraint');
        $constraint->setRegexValidation(Argument::type('bool'))->shouldBeCalled();

        $this->testValidationPrediction('pattern', $constraint);
    }

    function it_should_validate_additionalItems_as_either_boolean_or_object()
    {
        // Objects need to be valid schemas
        $objectConstraint = $this->prophesizeConstraint('ObjectConstraint');
        $objectConstraint->setSchemaValidation(Argument::type('bool'))->shouldBeCalled();

        // Bool constraint
        $booleanConstraint = $this->prophesizeConstraint('BooleanConstraint');

        $this->testValidationPrediction('additionalItems', [$objectConstraint, $booleanConstraint]);
    }

    function it_should_validate_items_as_either_object_or_arrays()
    {
        // Objects need to be valid schemas
        $objectConstraint = $this->prophesizeConstraint('ObjectConstraint');
        $objectConstraint->setSchemaValidation(Argument::type('bool'))->shouldBeCalled();

        $arrayConstraint = $this->prophesizeConstraint('ArrayConstraint');
        $arrayConstraint->setNestedSchemaValidation(Argument::type('bool'))->shouldBeCalled();

        $this->testValidationPrediction('items', [$objectConstraint, $arrayConstraint]);
    }

    function it_should_validate_maxItems_as_number_greater_than_or_equal_to_0()
    {
        $constraint = $this->prophesizeConstraint('NumberConstraint');
        $constraint->setLowerBound(0)->shouldBeCalled();
        $constraint->setExclusive(false)->shouldBeCalled();

        $this->testValidationPrediction('maxItems', $constraint);
    }

    function it_should_validate_minItems_as_number_greater_than_or_equal_to_0()
    {
        $constraint = $this->prophesizeConstraint('NumberConstraint');
        $constraint->setLowerBound(0)->shouldBeCalled();
        $constraint->setExclusive(false)->shouldBeCalled();

        $this->testValidationPrediction('minItems', $constraint);
    }

    function it_should_validate_uniqueItems_as_bool()
    {
        $constraint = $this->prophesizeConstraint('BooleanConstraint');
        $this->testValidationPrediction('uniqueItems', $constraint);
    }

    function it_should_validate_maxProperties_as_number_greater_than_or_equal_to_0()
    {
        $constraint = $this->prophesizeConstraint('NumberConstraint');
        $constraint->setLowerBound(0)->shouldBeCalled();
        $constraint->setExclusive(false)->shouldBeCalled();

        $this->testValidationPrediction('maxProperties', $constraint);
    }

    function it_should_validate_minProperties_as_number_greater_than_or_equal_to_0()
    {
        $constraint = $this->prophesizeConstraint('NumberConstraint');
        $constraint->setLowerBound(0)->shouldBeCalled();
        $constraint->setExclusive(false)->shouldBeCalled();

        $this->testValidationPrediction('minProperties', $constraint);
    }

    function it_should_validate_required_as_array()
    {
        $constraint = $this->prophesizeConstraint('ArrayConstraint');
        $constraint->setInternalType('string')->shouldBeCalled();
        $constraint->setUniqueness(true)->shouldBeCalled();
        $constraint->setMinimumCount(1)->shouldBeCalled();

        $this->testValidationPrediction('required', $constraint);
    }

    function it_should_validate_additionalProperties_as_either_object_or_bool()
    {
        // Objects need to be valid schemas
        $objectConstraint = $this->prophesizeConstraint('ObjectConstraint');
        $objectConstraint->setSchemaValidation(Argument::type('bool'))->shouldBeCalled();

        $boolConstraint = $this->prophesizeConstraint('BooleanConstraint');

        $this->testValidationPrediction('additionalProperties', [$objectConstraint, $boolConstraint]);
    }

    function it_should_validate_properties_as_object()
    {
        $constraint = $this->prophesizeConstraint('ObjectConstraint');
        $constraint->setNestedSchemaValidation(true)->shouldBeCalled();

        $this->testValidationPrediction('properties', $constraint);
    }

    function it_should_validate_patternProperties_as_object()
    {
        $constraint = $this->prophesizeConstraint('ObjectConstraint');
        $constraint->setPatternPropertiesValidation(true)->shouldBeCalled();

        $this->testValidationPrediction('patternProperties', $constraint);
    }

    function it_should_validate_dependencies_as_object()
    {
        $constraint = $this->prophesizeConstraint('ObjectConstraint');

        $this->testValidationPrediction('dependencies', $constraint);
    }

    function it_should_insist_on_dependencies_having_either_arrays_or_objects_as_object_properties()
    {
        $constraint = $this->prophesizeConstraint('ObjectConstraint');
        $constraint->setDependenciesValidation(true)->shouldBeCalled();

        $this->testValidationPrediction('dependencies', $constraint);
    }

    function it_should_validate_enum_as_array()
    {
        $constraint = $this->prophesizeConstraint('ArrayConstraint');
        $constraint->setMinimumCount(1)->shouldBeCalled();
        $this->testValidationPrediction('enum', $constraint);
    }

    function it_should_validate_type_as_array_of_strings_or_a_string_that_represent_primitive_types()
    {
        $arrayConstraint = $this->prophesizeConstraint('ArrayConstraint');
        $arrayConstraint->setMinimumCount(1)->shouldBeCalled();
        $arrayConstraint->setInternalPrimitiveTypeValidation(true)->shouldBeCalled();
        $arrayConstraint->setUniqueness(true)->shouldBeCalled();

        $stringConstraint = $this->prophesizeConstraint('StringConstraint');
        $stringConstraint->setPrimitiveTypeValidation(true)->shouldBeCalled();

        $this->testValidationPrediction('type', [$arrayConstraint, $stringConstraint]);
    }

    function it_should_validate_allOf_as_array_with_schema_objects()
    {
        $arrayConstraint = $this->prophesizeConstraint('ArrayConstraint');
        $arrayConstraint->setNestedSchemaValidation(true)->shouldBeCalled();
        $this->testValidationPrediction('allOf', $arrayConstraint);
    }

    function it_should_validate_anyOf_as_array_with_schema_objects()
    {
        $arrayConstraint = $this->prophesizeConstraint('ArrayConstraint');
        $arrayConstraint->setNestedSchemaValidation(true)->shouldBeCalled();
        $this->testValidationPrediction('anyOf', $arrayConstraint);
    }

    function it_should_validate_oneOf_as_array_with_schema_objects()
    {
        $arrayConstraint = $this->prophesizeConstraint('ArrayConstraint');
        $arrayConstraint->setNestedSchemaValidation(true)->shouldBeCalled();
        $this->testValidationPrediction('oneOf', $arrayConstraint);
    }

    function it_should_validate_not_as_valid_schema_object()
    {
        $objectConstraint = $this->prophesizeConstraint('ObjectConstraint');
        $objectConstraint->setSchemaValidation(true);
        $this->testValidationPrediction('not', $objectConstraint);
    }

    function it_should_validate_definitions_as_object_whose_members_are_schema_objects()
    {
        $objectConstraint = $this->prophesizeConstraint('ObjectConstraint');
        $objectConstraint->setNestedSchemaValidation(true);
        $this->testValidationPrediction('definitions', $objectConstraint);
    }

    /*** HELPER METHODS ***/

    private function getCollaboratorName(Collaborator $object)
    {
        $reflection = new \ReflectionObject($object->reveal());
        $namespace = $reflection->getNamespaceName();
        return substr($namespace, strrpos($namespace, '\\') + 1);
    }

    private function prophesizeConstraint($name)
    {
        $constraint = $this->prophet->prophesize('JsonSchema\\Validator\\Constraint\\' . $name);
        $constraint->willImplement('JsonSchema\Validator\Constraint\ConstraintInterface');
        return $constraint;
    }

    private function addConstraintPromises(ConstraintFactoryInterface $factory, ConstraintInterface $constraint)
    {
        $constraintName = $this->getCollaboratorName($constraint);
        $factory->create($constraintName, Argument::any(), Argument::any())->shouldBeCalled();
        $factory->create($constraintName, Argument::any(), Argument::any())->willReturn($constraint);
    }

    private function testValidationPrediction($name, $constraint)
    {
        $factory = $this->prophet->prophesize('JsonSchema\Validator\Constraint\ConstraintFactory');

        if (is_array($constraint)) {
            foreach ($constraint as $_constraint) {
                $this->addConstraintPromises($factory, $_constraint);
            }
        } else {
            $this->addConstraintPromises($factory, $constraint);
        }

        $this->setConstraintFactory($factory);
        $this->addKeywordConstraints($name, 'Foo');
    }
}