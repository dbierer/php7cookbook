<?php
namespace Application\Generic;
// shows the use of private to define a singleton class

class Registry
{
    protected static $instance = NULL;
    protected $registry = array();
    private function __construct()
    {
        // nobody can create an instance of this class
    }
    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    public function __get($key)
    {
        return $this->registry[$key] ?? NULL;
    }
    public function __set($key, $value)
    {
        $this->registry[$key] = $value;
    }
}
