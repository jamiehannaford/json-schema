<?php

class Parser
{
    private $jsonData;

    public function __construct($data)
    {
        $this->setJsonData($data);
    }

    public function __destruct()
    {
        if (is_resource($this->jsonData)) {
            fclose($this->jsonData);
        }
    }

    public function setJsonData($data)
    {
        if (is_string($data)) {
            $this->jsonData = (file_exists($data))
                ? fopen($data, 'r+')
                : self::createResourcefromString($data);
        } elseif (is_resource($data)) {
            $this->jsonData = $data;
        } else {
            throw new \InvalidArgumentException('Invalid JSON data type');
        }
    }

    public function getJsonData()
    {
        return $this->jsonData;
    }

    public static function createResourcefromString($string)
    {
        $stream = fopen('php://temp', 'r+');

        if ($string !== '') {
            fwrite($stream, $string);
            rewind($stream);
        }

        return $stream;
    }

    public function parse()
    {
        if (!is_resource($this->jsonData)) {
            throw new \RuntimeException('No JSON data has been set to parse');
        }
        return (new JsonSchema($this->jsonData));
    }
}
