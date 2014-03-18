<?php

namespace spec\Schema;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class RootSchemaSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('RootSchema');
    }
}