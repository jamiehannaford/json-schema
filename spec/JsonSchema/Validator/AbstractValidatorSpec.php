<?php

namespace spec\JsonSchema\Validator;

use JsonSchema\Validator\AbstractValidator;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class AbstractValidatorSpec extends ObjectBehavior
{
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

    function it_should_set_error_handler()
    {
        // @todo
    }
}

class TestableAbstractValidator extends AbstractValidator
{
    public function validate() {}

    protected function declareValidationFailure($keyword, $value, $constraintName, $result) {}
}