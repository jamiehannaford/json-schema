<?php

namespace spec\JsonSchema\Validator;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class FailureEventSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('JsonSchema\Validator\FailureEvent');
        $this->shouldHaveType('Symfony\Component\EventDispatcher\Event');
    }

    function let()
    {
        $data = ['foo' => 'bar'];

        $this->beConstructedWith($data);
    }

    function it_should_have_internal_data()
    {
        $this->offsetGet('foo')->shouldReturn('bar');
    }

    function it_should_return_all_data()
    {
        $this->getData()->shouldReturn(['foo' => 'bar']);
    }
}