<?php

namespace spec\JsonSchema\Validator;

use JsonSchema\Schema\RootSchema;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Prophecy\Prophet;

class InstanceValidatorSpec extends ObjectBehavior
{
    private $prophet;

    function let()
    {
        $this->prophet = new Prophet();
    }

    function letgo()
    {
        //$this->prophet->checkPredictions();
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('JsonSchema\Validator\InstanceValidator');
        $this->shouldImplement('JsonSchema\Validator\ValidatorInterface');
    }

    function it_should_have_schema(RootSchema $schema)
    {
        $this->setSchema($schema);
        $this->getSchema()->shouldReturn($schema);
    }

    function it_should_pass_validation_if_value_divided_by_multipleOf_results_in_positive_int()
    {
        $schema = (object)['multipleOf' => 2];
        $this->setSchema($this->makeSchema($schema));

        $this->setData(7);
        $this->validate()->shouldReturn(false);

        $this->setData(2);
        $this->validate()->shouldReturn(true);
    }

    private function makeSchema($data)
    {
        $schema = $this->prophet->prophesize('JsonSchema\Schema\RootSchema');
        $schema->willImplement('JsonSchema\Schema\SchemaInterface');

        $schema->setData($data);
        return $schema;
    }
}