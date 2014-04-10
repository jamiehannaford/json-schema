<?php

namespace spec\JsonSchema\Validator;

use JsonSchema\Schema\RootSchema;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class InstanceValidatorSpec extends ObjectBehavior
{
    use HasValidationChecker;

    private function makeSchema(array $data)
    {
        $schema = $this->prophet->prophesize('JsonSchema\Schema\RootSchema');
        $schema->willImplement('JsonSchema\Schema\SchemaInterface');
        $schema->setData((object) $data);
        return $schema;
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('JsonSchema\Validator\InstanceValidator');
        $this->shouldImplement('JsonSchema\Validator\ValidatorInterface');
    }

    function it_should_have_schema(RootSchema $schema)
    {
        $this->setSchema($schema);
        $this->getSchema()->shouldReturn($schema);
    }

    /*** NUMERIC TYPES ***/

    function it_should_pass_validation_if_value_divided_by_multipleOf_results_in_positive_int()
    {
        $constraint = $this->prophesizeConstraint('NumberConstraint');
        $constraint->setMultipleOf(Argument::type('int'))->shouldBeCalled();

        $this->testValidationPrediction('multipleOf', $constraint, 2);
    }

    function it_should_validate_if_exclusiveMax_is_null_or_false_and_val_is_lower_or_equal_to_maximum()
    {
        $max = 10;

        $schema = ['exclusiveMaximum' => false, 'maximum' => $max];
        $this->setSchema($this->makeSchema($schema));

        $constraint = $this->prophesizeConstraint('NumberConstraint');
        $constraint->setHigherBound($max)->shouldBeCalled();
        $constraint->setExclusive(true)->shouldNotBeCalled();

        $this->testValidationPrediction('maximum', $constraint, $max);
    }

    function it_should_validate_if_exclusiveMax_is_true_and_val_is_lower_than_maximum()
    {
        $max = 10;

        $schema = $this->makeSchema(['exclusiveMaximum' => true, 'maximum' => $max]);
        $schema->offsetGet('exclusiveMaximum')->willReturn(true);
        $this->setSchema($schema);

        $constraint = $this->prophesizeConstraint('NumberConstraint');
        $constraint->setHigherBound($max)->shouldBeCalled();
        $constraint->setExclusive(true)->shouldBeCalled();

        $this->testValidationPrediction('maximum', $constraint, $max);
    }

    function it_should_validate_if_exclusiveMin_is_null_or_false_and_val_is_higher_or_equal_to_minimum()
    {
        $min = 10;

        $schema = ['exclusiveMinimum' => false, 'minimum' => $min];
        $this->setSchema($this->makeSchema($schema));

        $constraint = $this->prophesizeConstraint('NumberConstraint');
        $constraint->setLowerBound($min)->shouldBeCalled();
        $constraint->setExclusive(true)->shouldNotBeCalled();

        $this->testValidationPrediction('minimum', $constraint, $min);
    }

    function it_should_validate_if_exclusiveMin_is_true_and_val_is_higher_than_minimum()
    {
        $min = 10;

        $schema = $this->makeSchema(['exclusiveMinimum' => true, 'minimum' => $min]);
        $schema->offsetGet('exclusiveMinimum')->willReturn(true);
        $this->setSchema($schema);

        $constraint = $this->prophesizeConstraint('NumberConstraint');
        $constraint->setLowerBound($min)->shouldBeCalled();
        $constraint->setExclusive(true)->shouldBeCalled();

        $this->testValidationPrediction('minimum', $constraint, $min);
    }

    /*** STRING TYPES ***/

    function it_should_validate_if_string_length_is_less_than_or_equal_to_maxLength()
    {
        $maxLength = 100;

        $schema = $this->makeSchema(['maxLength' => $maxLength]);
        $this->setSchema($schema);

        $constraint = $this->prophesizeConstraint('StringConstraint');
        $constraint->setMaxLength($maxLength)->shouldBeCalled();

        $this->testValidationPrediction('maxLength', $constraint, $maxLength);
    }

    function it_should_validate_if_string_length_is_more_than_or_equal_to_minLength()
    {
        $minLength = 100;

        $schema = $this->makeSchema(['minLength' => $minLength]);
        $this->setSchema($schema);

        $constraint = $this->prophesizeConstraint('StringConstraint');
        $constraint->setMinLength($minLength)->shouldBeCalled();

        $this->testValidationPrediction('minLength', $constraint, $minLength);
    }

    function it_should_validate_if_string_matches_regex_pattern()
    {
        $regex = '#something#';

        $schema = $this->makeSchema(['pattern' => $regex]);
        $this->setSchema($schema);

        $constraint = $this->prophesizeConstraint('StringConstraint');
        $constraint->setRegexValidation(true)->shouldBeCalled();

        $this->testValidationPrediction('pattern', $constraint, $regex);
    }

    /*** ARRAY TYPES ***/

    function it_should_validate_if_items_is_not_present_regardless_of_additionalItems()
    {
        $schema = $this->makeSchema(['additionalItems' => false]);
        $this->setSchema($schema);

        $constraint = $this->prophesizeConstraint('ArrayConstraint');

        $this->testValidationPrediction('items', $constraint, null);
    }

    function it_should_validate_if_items_is_an_object_regardless_of_additionalItems()
    {
        $schema = $this->makeSchema(['additionalItems' => false, 'items' => new \stdClass()]);
        $this->setSchema($schema);

        $constraint = $this->prophesizeConstraint('ArrayConstraint');

        $this->testValidationPrediction('items', $constraint, null);
    }

    function it_should_validate_if_additionalItems_is_true()
    {
        $schema = $this->makeSchema(['additionalItems' => true]);
        $this->setSchema($schema);

        $constraint = $this->prophesizeConstraint('ArrayConstraint');

        $this->testValidationPrediction('items', $constraint, null);
    }

    function it_should_validate_if_additionalItems_is_an_object()
    {
        $schema = $this->makeSchema(['additionalItems' => true]);
        $this->setSchema($schema);

        $constraint = $this->prophesizeConstraint('ArrayConstraint');

        $this->testValidationPrediction('items', $constraint, null);
    }

    function it_should_validate_if_additionalItems_is_false_and_items_is_an_array()
    {
        $items = [1, 'foo', [], 'bar'];
        $schema = $this->makeSchema(['additionalItems' => false, 'items' => $items]);

        $schema->offsetGet('additionalItems')->willReturn(false);
        $schema->offsetGet('items')->willReturn($items);

        $this->setSchema($schema);

        $constraint = $this->prophesizeConstraint('ArrayConstraint');
        $constraint->setMaximumCount(count($items))->shouldBeCalled();

        $this->testValidationPrediction('items', $constraint, $items);
    }

    function it_should_validate_if_array_count_is_less_than_or_equal_to_maxItems()
    {
        $maxItems = 3;
        $schema = $this->makeSchema(['maxItems' => $maxItems]);
        $this->setSchema($schema);

        $constraint = $this->prophesizeConstraint('ArrayConstraint');
        $constraint->setMaximumCount($maxItems)->shouldBeCalled();

        $this->testValidationPrediction('maxItems', $constraint, $maxItems);
    }

    function it_should_validate_if_array_count_is_more_than_or_equal_to_minItems()
    {
        $minItems = 3;
        $schema = $this->makeSchema(['minItems' => $minItems]);
        $this->setSchema($schema);

        $constraint = $this->prophesizeConstraint('ArrayConstraint');
        $constraint->setMinimumCount($minItems)->shouldBeCalled();

        $this->testValidationPrediction('minItems', $constraint, $minItems);
    }

    function it_should_validate_if_array_has_unique_items_and_uniqueItems_is_true()
    {
        $uniqueItems = true;
        $schema = $this->makeSchema(['uniqueItems' => $uniqueItems]);
        $this->setSchema($schema);

        $constraint = $this->prophesizeConstraint('ArrayConstraint');
        $constraint->setUniqueItems(true)->shouldBeCalled();

        $this->testValidationPrediction('uniqueItems', $constraint, $uniqueItems);
    }

    function it_should_validate_if_array_has_unique_items_and_uniqueItems_is_false()
    {
        $uniqueItems = false;
        $schema = $this->makeSchema(['uniqueItems' => $uniqueItems]);
        $this->setSchema($schema);

        $constraint = $this->prophesizeConstraint('ArrayConstraint');
        $constraint->setUniqueItems(true)->shouldNotBeCalled();

        $this->testValidationPrediction('uniqueItems', $constraint, $uniqueItems);
    }

    /*** OBJECT TYPES ***/

    function it_should_validate_if_object_property_count_is_less_than_or_equal_to_max_properties()
    {
        $maxProperties = 5;
        $schema = $this->makeSchema(['maxProperties' => $maxProperties]);
        $this->setSchema($schema);

        $constraint = $this->prophesizeConstraint('ObjectConstraint');
        $constraint->setMaxProperties($maxProperties)->shouldBeCalled();

        $this->testValidationPrediction('maxProperties', $constraint, $maxProperties);
    }

    function it_should_validate_if_object_property_count_is_more_than_or_equal_to_min_properties()
    {
        $minProperties = 2;
        $schema = $this->makeSchema(['minProperties' => $minProperties]);
        $this->setSchema($schema);

        $constraint = $this->prophesizeConstraint('ObjectConstraint');
        $constraint->setMinProperties($minProperties)->shouldBeCalled();

        $this->testValidationPrediction('minProperties', $constraint, $minProperties);
    }

    function it_should_validate_if_object_contains_every_element_in_required_array()
    {
        $required = ['name', 'age', 'location'];
        $schema = $this->makeSchema(['required' => $required]);
        $this->setSchema($schema);

        $constraint = $this->prophesizeConstraint('ObjectConstraint');
        $constraint->setRequiredElementNames($required)->shouldBeCalled();

        $this->testValidationPrediction('required', $constraint, $required);
    }

    function it_should_validate_if_additionalProperties_is_true()
    {
        $schema = $this->makeSchema(['additionalProperties' => true]);
        $this->setSchema($schema);

        $constraint = $this->prophesizeConstraint('ObjectConstraint');
        $constraint->setStrictAdditionalProperties(true)->shouldNotBeCalled();

        $this->testValidationPrediction('additionalProperties', $constraint, true);
    }

    function it_should_validate_properties_using_additionalProperties()
    {
        $properties = (object)['foo' => (object)['title' => 'bar']];

        $schema = $this->makeSchema([
            'additionalProperties' => false,
            'properties'           => $properties
        ]);

        $schema->offsetGet('additionalProperties')->willReturn(false);
        $schema->offsetGet('additionalProperties')->shouldBeCalled();

        $schema->offsetGet('patternProperties')->willReturn(null);

        $schema->offsetGet('properties')->willReturn($properties);
        $schema->offsetGet('properties')->shouldBeCalled();

        $this->setSchema($schema);

        $constraint = $this->prophesizeConstraint('ObjectConstraint');
        $constraint->setStrictAdditionalProperties(true)->shouldBeCalled();
        $constraint->setAllowedPropertyNames($properties)->shouldBeCalled();

        $this->testValidationPrediction('properties', $constraint, $properties);
    }

    function it_should_validate_patternProperties_using_additionalProperties()
    {
        $properties = (object)['#foo#' => (object)['title' => 'bar']];

        $schema = $this->makeSchema([
            'additionalProperties' => false,
            'patternProperties'    => $properties
        ]);

        $schema->offsetGet('additionalProperties')->willReturn(false);
        $schema->offsetGet('additionalProperties')->shouldBeCalled();

        $schema->offsetGet('properties')->willReturn(null);

        $schema->offsetGet('patternProperties')->willReturn($properties);
        $schema->offsetGet('patternProperties')->shouldBeCalled();

        $this->setSchema($schema);

        $constraint = $this->prophesizeConstraint('ObjectConstraint');
        $constraint->setStrictAdditionalProperties(true)->shouldBeCalled();
        $constraint->setRegexArray($properties)->shouldBeCalled();

        $this->testValidationPrediction('patternProperties', $constraint, $properties);
    }

    function it_should_validate_schema_dependencies()
    {
        $schemaDependencies = (object)['foo' => (object)['enum' => ['foo', 'bar', 'baz']]];

        $schema = $this->makeSchema(['dependencies' => $schemaDependencies]);

        $this->setSchema($schema);

        $constraint = $this->prophesizeConstraint('ObjectConstraint');
        $constraint->setDependenciesInstanceValidation(true)->shouldBeCalled();
        $constraint->setSchemaDependencies($schemaDependencies)->shouldBeCalled();

        $this->testValidationPrediction('dependencies', $constraint, $schemaDependencies);
    }

    function it_should_validate_property_dependencies()
    {
        $propertyDependencies = ['foo', 'bar'];

        $schema = $this->makeSchema(['dependencies' => $propertyDependencies]);
        $this->setSchema($schema);

        $constraint = $this->prophesizeConstraint('ObjectConstraint');
        $constraint->setDependenciesInstanceValidation(true)->shouldBeCalled();
        $constraint->setAllowedPropertyNames($propertyDependencies)->shouldBeCalled();

        $this->testValidationPrediction('dependencies', $constraint, $propertyDependencies);
    }

    function it_should_validate_enum()
    {
        $enum = ['foo', 'bar'];

        $schema = $this->makeSchema(['enum' => $enum]);
        $this->setSchema($schema);

        $constraint = $this->prophesizeConstraint('GenericConstraint');
        $constraint->setEnumValues($enum)->shouldBeCalled();

        $this->testValidationPrediction('enum', $constraint, $enum);
    }

    function it_should_validate_type_strings()
    {
        $type = 'array';

        $schema = $this->makeSchema(['type' => $type]);
        $this->setSchema($schema);

        $constraint = $this->prophesizeConstraint('ArrayConstraint');

        $this->testValidationPrediction('type', $constraint, $type);
    }

    function it_should_validate_type_arrays()
    {
        $types = ['array', 'integer'];

        $schema = $this->makeSchema(['type' => $types]);
        $this->setSchema($schema);

        $arrayConstraint = $this->prophesizeConstraint('ArrayConstraint');
        $stringConstraint = $this->prophesizeConstraint('NumberConstraint');

        $this->testValidationPrediction('type', [$arrayConstraint, $stringConstraint], $types);
    }
}