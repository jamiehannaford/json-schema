<?php

class JsonSchema implements \ArrayAccess
{
    private $data;

    private $keywords = [
        'title'       => 'string',
        'description' => 'string',
        'multipleOf'  => 'setMultipleOf'
    ];

    private function setValue($offset, $value)
    {
        $this->data[$offset] = $value;
    }

    public function offsetSet($offset, $value)
    {
        // Are we setting a keyword?
        if (array_key_exists($offset, $this->keywords)) {
            $type = $this->keywords[$offset];
            $setItemMethod = "setItemAs{$type}";

            if (substr($type, 0, 3) == 'set') {
                // Call custom setter method, i.e. setMultipleOf($val)
                return call_user_func_array([$this, $type], [$value]);
            } elseif (method_exists($this, $setItemMethod)) {
                // Call generic type setter method, i.e. setItemAsString($key, $val)
                return call_user_func_array([$this, $setItemMethod], [$offset, $value]);
            } else {
                // This keyword cannot be set
                throw new \RuntimeException(sprintf("Invalid data/setter type for %s", $offset));
            }
        }

        // Setting a normal value
        $this->setValue($offset, $value);
    }

    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->data);
    }

    public function offsetUnset($offset)
    {
        unset($this->data[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->offsetExists($offset) ? $this->data[$offset] : null;
    }

    private function setItemAsString($key, $value)
    {
        if (is_array($value) || is_object($value) || is_resource($value)) {
            throw new \InvalidTypeException($key, $value, "string");
        }

        $this->setValue($key, (string) $value);
    }

    private function setItemAsInteger($key, $value, $min = false, $max = false)
    {
        if (!is_numeric($value)) {
            throw new \InvalidTypeException($key, $value, "positive integer");
        }

        if ($min !== false && $value <= $min) {
            $message = sprintf(
                "\"%s\" must be a positive integer greater than %s, you provided %s",
                $key, $min, $value
            );
        }

        if ($max !== false && $value >= $max) {
            $message = sprintf(
                "\"%s\" must be a positive integer less than %d, you provided %s",
                $key, $max, $value
            );
        }

        if (isset($message)) {
            throw new \InvalidArgumentException($message);
        }

        $this->setValue($key, (int) $value);
    }

    public function setMultipleOf($value)
    {
        $this->setItemAsInteger('multipleOf', $value, 0);
    }
}
