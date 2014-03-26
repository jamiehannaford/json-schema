<?php

namespace spec\JsonSchema\Validator;

use JsonSchema\Enum\StrictnessMode;
use JsonSchema\Validator\AbstractValidator;
use JsonSchema\Validator\Constraint\StringConstraint;
use JsonSchema\Validator\ErrorHandler\BufferErrorHandler;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class AbstractValidatorSpec extends ObjectBehavior
{
    public function getMatchers()
    {
        return [
            'contain' => function ($subject, $key) {
                    return array_search($key, $subject);
                },
            'beCount' => function ($subject, $num) {
                    return count($subject) === $num;
                }
        ];
    }

    function let()
    {
        $this->beAnInstanceOf('spec\JsonSchema\Validator\TestableAbstractValidator');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('JsonSchema\Validator\AbstractValidator');
        $this->shouldImplement('JsonSchema\Validator\ValidatorInterface');
    }

    function it_should_set_data()
    {
        $value = ['foo' => 'bar'];
        $this->setData($value)->shouldBeNull();
        $this->getData()->shouldReturn($value);
    }

    function it_should_set_error_handler(BufferErrorHandler $handler)
    {
        $this->setErrorHandler($handler);
        $this->getErrorHandler()->shouldReturnAnInstanceOf('JsonSchema\Validator\ErrorHandler\ErrorHandlerInterface');
    }

    function it_should_allow_addition_of_constraint(StringConstraint $constraint)
    {
        $this->addConstraint($constraint);
        $this->getConstraints()->shouldContain($constraint);

        $this->setConstraints([]);
        $this->getConstraints()->shouldBeCount(0);
    }

    function it_should_provide_easy_instantiation_of_constraint_classes()
    {
        $this->createConstraint('StringConstraint', 'Foo')->shouldReturnAnInstanceOf('JsonSchema\Validator\Constraint\ConstraintInterface');
    }

    function it_should_set_strictness_mode_as_all_by_default()
    {
        $this->getStrictnessMode()->shouldReturn(StrictnessMode::ALL);
    }

    function it_should_allow_different_strictness_modes()
    {
        $this->setStrictnessMode(StrictnessMode::ANY);
        $this->getStrictnessMode()->shouldReturn(StrictnessMode::ANY);
    }

    function it_considers_validation_successful_if_every_constraint_passes_when_strictness_mode_is_all()
    {

    }

    function it_considers_validation_unsuccessful_if_any_constraint_fails_when_strictness_mode_is_all()
    {

    }

    function it_considers_validation_successful_if_any_constraint_passes_when_strictness_mode_is_any()
    {

    }

    function it_considers_validation_unsuccessful_if_all_constraints_fail_when_strictness_mode_is_any()
    {

    }
}

class TestableAbstractValidator extends AbstractValidator
{
    public function validate() {}

    protected function declareValidationFailure($keyword, $value, $constraintName, $result) {}
}