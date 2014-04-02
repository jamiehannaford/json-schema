<?php

namespace spec\JsonSchema;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ParserSpec extends ObjectBehavior
{
    const COMPUTE_FIXTURES = 'compute.json';

    protected function getFixturesFile($path = self::COMPUTE_FIXTURES)
    {
        return dirname(__DIR__) . '/fixtures/' . $path;
    }

    function let()
    {
        $this->beConstructedWith($this->getFixturesFile());
    }

    public function getMatchers()
    {
        return [
            'haveStreamUri' => function($stream, $uri) {
                return stream_get_meta_data($stream)['uri'] == $uri;
            }
        ];
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('JsonSchema\Parser');
    }

    function it_should_accept_strings_as_paths_where_possible()
    {
        $path = $this->getFixturesFile();
        $this->setJsonData($path);
        $this->getJsonData()->shouldBeResource();
        $this->getJsonData()->shouldHaveStreamUri($path);
    }

    function it_should_otherwise_load_strings_to_tmp_streams()
    {
        $string = str_repeat('A', 500);
        $this->setJsonData($string);
        $this->getJsonData()->shouldBeResource();
        $this->getJsonData()->shouldHaveStreamUri('php://temp');
    }

    function it_should_accept_resources()
    {
        $resource = fopen($this->getFixturesFile(), 'r+');
        $this->setJsonData($resource);
        $this->getJsonData()->shouldBeResource();
    }

    function it_should_throw_error_for_any_other_data_type()
    {
        $this->shouldThrow('\InvalidArgumentException')->duringSetJsonData([]);
        $this->shouldThrow('\InvalidArgumentException')->duringSetJsonData((object) []);
        $this->shouldThrow('\InvalidArgumentException')->duringSetJsonData(null);
        $this->shouldThrow('\InvalidArgumentException')->duringSetJsonData(false);
    }

    function it_should_return_a_json_schema_object()
    {
        $this->parse()->shouldReturnAnInstanceOf('JsonSchema');
    }
}
