<?php
namespace Application\Event;

class Manager
{
    public $alias = array();
    public $listeners = array();
    public function attach($event, $callback)
    {
        $this->listeners[$event][] = $callback;
    }
    public function trigger($event, $params)
    {
        if (!isset($this->listeners[$event])) {
            return $this->listeners[$this->alias[$event]]($params);
        } else {
            return $this->listeners[$event]($params);
        }
    }
    public function setAlias($alias, $event)
    {
        $this->alias[$event] = $alias;
    }
}
    
