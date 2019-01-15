<?php
class Test
{
    protected $test = 'TEST';
    public function getTest()
    {
        return $this->test;
    }
}

class test
{
    protected $test = 'TEST';
    public function getTest()
    {
        return $this->test;
    }
}

$t = new Test();
echo $t->getTest();

