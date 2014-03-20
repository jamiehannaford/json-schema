<?php

namespace spec\JsonSchema\Validator\ErrorHandler;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\Event;

class BufferErrorHandlerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('JsonSchema\Validator\ErrorHandler\BufferErrorHandler');
        $this->shouldImplement('JsonSchema\Validator\ErrorHandler\ErrorHandlerInterface');
    }

    function it_should_store_errors_when_received(Event $event)
    {
        $errors = array_fill(0, 10, $event);

        foreach ($errors as $error) {
            $this->receiveError($error);
        }

        $this->getErrorCount()->shouldReturn(10);
    }
}
