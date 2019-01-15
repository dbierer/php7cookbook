<?php
namespace Application\Generic;

class Tree
{
    protected $values = array();
    public function __set($key, $value)
    {
        $this->values[$key] = $value;
    }
    public function __get($key)
    {
        return $this->values[$key] ?? NULL;
    }
}
