<?php

namespace spec\JsonSchema\Validator\Constraint;

use JsonSchema\Enum\LogType;
use JsonSchema\Validator\Constraint\AbstractConstraint;
use JsonSchema\Validator\ErrorHandler\BufferErrorHandler;
use JsonSchema\Validator\FailureEvent;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Prophecy\Prophet;
use spec\JsonSchema\Validator\HasValidationChecker;
use Symfony\Component\EventDispatcher\EventDispatcher;

class AbstractConstraintSpec extends ObjectBehavior
{
    use HasValidationChecker;

    const VALUE = 'Foo';
    const NAME  = 'Baz';

    function let(EventDispatcher $dispatcher, BufferErrorHandler $handler)
    {
        $this->beAnInstanceOf('spec\JsonSchema\Validator\Constraint\TestableAbstractConstraint');
        $this->beConstructedWith(self::NAME, self::VALUE, $dispatcher);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('JsonSchema\Validator\Constraint\AbstractConstraint');
    }

    function it_should_have_mutable_value()
    {
        $this->setValue(self::VALUE);
        $this->getValue()->shouldReturn(self::VALUE);
    }

    function it_should_have_mutable_name()
    {
        $this->setName(self::NAME);
        $this->getName()->shouldReturn(self::NAME);
    }

    function it_should_allow_easy_creation_of_root_schema_object()
    {
        $data = (object)['foo' => 'bar'];
        $this->createRootSchema($data)->shouldReturnAnInstanceOf('JsonSchema\Schema\SchemaInterface');
    }

    function it_should_not_validate_invalid_regex()
    {
        $this->validateRegex('#foo')->shouldReturn(false);
        $this->validateRegex('#foo#')->shouldReturn(true);
    }

    function it_should_support_enum_values()
    {
        $array = ['foo', [], 'bar'];
        $this->setEnumValues($array);
        $this->getEnumValues()->shouldReturn($array);
    }

    function it_should_fail_validation_if_value_does_not_equal_an_enum_value()
    {
        $this->setOverrideTypeCheck(true);
        $this->setEnumValues(['foo']);
        $this->setValue('bar');

        $this->testFailureDispatch(self::NAME, 'bar', "Value does not match enum array");
        $this->validate()->shouldReturn(false);
    }

    function it_should_pass_validation_if_value_is_in_enum_value()
    {
        $this->setOverrideTypeCheck(true);

        $empty = new \stdClass();
        $this->setEnumValues([$empty]);
        $this->setValue($empty);

        $this->validate()->shouldReturn(true);
    }

    function it_should_support_type()
    {
        $this->setType('array');
        $this->getType()->shouldReturn('array');
    }

    function it_should_throw_exception_if_type_is_not_native_type()
    {
        $this->shouldThrow('InvalidArgumentException')->duringSetType('foo');
    }

    function it_should_pass_validation_if_instance_type_matches_set_type()
    {
        $this->setOverrideTypeCheck(true);

        $this->setType('array');
        $this->setValue(['foo' => 'bar']);

        $this->validate()->shouldReturn(true);
    }

    function it_should_fail_validation_if_instance_type_does_not_match_set_type()
    {
        $this->setType('object');
        $this->setValue(99.9);

        $this->testFailureDispatch(self::NAME, 99.9, "Type is incorrect", 'object');
        $this->validate()->shouldReturn(false);
    }

    function it_should_pass_validation_if_instance_type_is_in_type_array()
    {
        $this->setOverrideTypeCheck(true);

        $this->setType(['array', 'string']);
        $this->setValue(['foo' => 'bar']);

        $this->validate()->shouldReturn(true);
    }

    function it_should_support_generic_failure_logging(EventDispatcher $dispatcher)
    {
        $dispatcher->dispatch(Argument::type('string'), Argument::type('Symfony\Component\EventDispatcher\Event'))->shouldBeCalled();
        $this->setEventDispatcher($dispatcher);
        $this->logFailure(Argument::any())->shouldReturn(false);
    }

    function it_should_support_log_types()
    {
        $this->setLogType(LogType::DISABLED);
        $this->getLogType()->shouldReturn(LogType::DISABLED);
    }

    function it_should_have_emitting_as_default_log_type()
    {
        $this->getLogType()->shouldReturn(LogType::EMITTING);
    }

    function it_should_emit_no_errors_if_logging_is_disabled()
    {
        $this->setName('foo');
        $this->setType('object');
        $this->setValue(99.9);

        $event = new FailureEvent([
            'name'  => 'foo',
            'value' => 99.9,
            'message' => 'Type is incorrect',
            'expected' => 'object'
        ]);

        $prophet = new Prophet();
        $dispatcher = $prophet->prophesize('Symfony\Component\EventDispatcher\EventDispatcher');
        $dispatcher->dispatch('validation.error', $event)->shouldNotBeCalled();
        $this->setEventDispatcher($dispatcher);

        $this->setLogType(LogType::DISABLED);

        $this->validate();
    }

    function it_should_store_errors_if_logging_set_to_internal()
    {
        $this->setType('object');
        $this->setValue(99.9);

        $this->setLogType(LogType::INTERNAL);

        $this->validate();

        $this->getValidationErrors()->shouldHaveCount(1);
    }
}

class TestableAbstractConstraint extends AbstractConstraint
{
    private $overrideTypeCheck = false;

    public function setOverrideTypeCheck($choice)
    {
        $this->overrideTypeCheck = (bool) $choice;
    }

    public function hasCorrectType()
    {
        if (true === $this->overrideTypeCheck) {
            return true;
        }

        return false;
    }

    public function validateConstraint()
    {
        return true;
    }
}