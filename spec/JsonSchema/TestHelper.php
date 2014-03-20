<?php

namespace spec\JsonSchema;

class TestHelper 
{
    public static function getWrongDataTypes($correctType)
    {
        $allTypes = [
            'string'   => 'foo',
            'int'      => 1,
            'float'    => 2.5,
            'bool'     => true,
            'object'   => new \stdClass(),
            'array'    => [],
            'resource' => fopen('php://temp', 'r+')
        ];

        if ($correctType == 'numeric') {
            $correctType = ['int', 'float'];
        }

        $correctTypes = array_flip((array) $correctType);

        return array_diff_key($allTypes, $correctTypes);
    }

    /**
     * Checks a keyword type validity
     *
     * @param $keyword
     * @param $correctType
     */
    private function testDataType($keyword, $correctType)
    {
        $incorrectTypes = self::getWrongDataTypes($correctType);
        $correctTypes   = array_flip((array) $correctType);

        foreach ($incorrectTypes as $value) {
            $exception = InvalidTypeException::factory($keyword, $value, $correctTypes);
            $this->shouldThrow($exception)->duringOffsetSet($keyword, $value);
        }

        fclose($incorrectTypes['resource']);
    }

    /**
     * Test that a number is greater than a minimum
     *
     * @param $keyword Name of keyword
     * @param $min     Integer the value must be greater than
     */
    private function testNumberIsGreaterThan($keyword, $min = 0)
    {
        // Make a random int less than the given minimum
        $wrongInt = ($min - rand(1, 10));

        // Test that out of bounds integers are caught
        $exception = new \InvalidArgumentException(sprintf(
            "\"%s\" must be a positive integer greater than %d, you provided %d",
            $keyword, $min, $wrongInt
        ));
        $this->shouldThrow($exception)->duringOffsetSet($keyword, $wrongInt);
    }
}