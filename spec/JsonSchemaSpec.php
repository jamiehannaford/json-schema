<?php

namespace spec;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Prophecy\Exception\InvalidArgumentException;

class JsonSchemaSpec extends ObjectBehavior
{
    const RANDOM_STRING = 'Foo';

    function it_is_initializable()
    {
        $this->shouldHaveType('JsonSchema');
        $this->shouldImplement('\ArrayAccess');
    }

    function it_should_have_a_mutable_title_in_string_form()
    {
        $this->offsetSet('title', self::RANDOM_STRING);
        $this->offsetGet('title')->shouldReturn(self::RANDOM_STRING);

        $this->offsetSet('title', false);
        $this->offsetGet('title')->shouldBeString();

        $this->offsetSet('title', 2345);
        $this->offsetGet('title')->shouldBeString();
    }

    function it_should_have_a_mutable_desc_in_string_form()
    {
        $this->offsetSet('description', self::RANDOM_STRING);
        $this->offsetGet('description')->shouldReturn(self::RANDOM_STRING);

        $this->offsetSet('description', null);
        $this->offsetGet('description')->shouldBeString();
    }

    function it_should_throw_exception_if_casting_val_to_string_is_impossible()
    {
        $this->shouldThrow('\InvalidArgumentException')->duringOffsetSet('description', []);

        $exception = new \InvalidArgumentException('"description" must be a string, you provided an object');
        $this->shouldThrow($exception)->duringOffsetSet('description', new \stdClass());

        $exception = new \InvalidArgumentException('"title" must be a string, you provided a resource');
        $resource = fopen('php://temp', 'r+');
        $this->shouldThrow($exception)->duringOffsetSet('title', $resource);
        fclose($resource);
    }

    function it_should_support_multiple_of_keyword()
    {
        $this->offsetSet('multipleOf', 50);
        $this->offsetGet('multipleOf')->shouldReturn(50);
    }

    function it_should_throw_exception_when_setting_multiple_of_without_natural_number()
    {
        $this->shouldThrow('\InvalidArgumentException')->duringOffsetSet('multipleOf', []);
        $this->shouldThrow('\InvalidArgumentException')->duringOffsetSet('multipleOf', 0);

        $exception = new \InvalidArgumentException('"multipleOf" must be a positive integer greater than 0, you provided -1');
        $this->shouldThrow($exception)->duringOffsetSet('multipleOf', -1);

        $exception = new \InvalidArgumentException('"multipleOf" must be a positive integer, you provided a string');
        $this->shouldThrow($exception)->duringOffsetSet('multipleOf', 'string');
    }


}