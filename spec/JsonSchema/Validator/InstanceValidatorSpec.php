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
}