<?php

namespace spec\JsonSchema;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ModelSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('JsonSchema\Model');
        $this->shouldImplement('JsonSchema\ModelInterface');
        $this->shouldImplement('\ArrayAccess');
    }
}
