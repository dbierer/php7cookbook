<?php
// dispatching stuff to the web
namespace Application\Mvc;

use Application\Mvc\Nav;
use Application\Web\Hoover;

class Dispatch
{

    const KEY_SEPARATOR = '-+-';
    
    public $config = array();
    public $vac;
    public $lang = 'en';
    
    public function __construct($config)
    {
        $this->config = $config;
        $this->vac    = new Hoover();
        $this->lang   = $_SESSION['lang'][0] ?? 'en';
    }
    
    public function __invoke($obj, $call, $key)
    {
        return (new $obj())->$call($this->config[$key], $this);
    }

    public function callback($key)
    {
        return $this->config['callback'][$key]->call($this);
    }
}
