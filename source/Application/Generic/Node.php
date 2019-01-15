<?php
namespace Application\Generic;

class Node extends Tree
{
    public function __construct($name)
    {
        $this->__set('name', $name);
    }
}
