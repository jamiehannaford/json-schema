<?php

namespace spec\JsonSchema\Validator\ErrorHandler;

use JsonSchema\Validator\ErrorHandler\AbstractErrorHandler;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\Event;

class AbstractErrorHandlerSpec extends ObjectBehavior
{
    function let()
    {
        $this->beAnInstanceOf('spec\JsonSchema\Validator\ErrorHandler\TestableAbstractErrorHandler');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('JsonSchema\Validator\ErrorHandler\AbstractErrorHandler');
        $this->shouldImplement('JsonSchema\Validator\ErrorHandler\ErrorHandlerInterface');
    }

    function it_should_contain_appropriate_events()
    {
        $this->getSubscribedEvents()->shouldReturn([
            'validation.error' => 'receiveError'
        ]);
    }

    function it_should_count_errors()
    {
        $this->getErrorCount()->shouldReturn(0);
    }

    function it_should_return_errors_as_array()
    {
        $this->getErrors()->shouldReturn([]);
    }
}

class TestableAbstractErrorHandler extends AbstractErrorHandler
{
    public function receiveError(Event $event) {}
}