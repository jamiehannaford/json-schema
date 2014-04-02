<?php

namespace spec\JsonSchema\Schema;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class SubSchemaSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('SubSchema');
    }
}