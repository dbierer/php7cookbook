<?php
namespace Application\PubSub;

/**
 * NOTE: this actually implements Subject/Observer, not PubSub!!!
 */

use SplSubject;
use SplObserver;

class Subscriber implements SplObserver
{
	protected $key;
    protected $name;
    protected $priority;
    protected $callback;
    public function __construct(string $name, callable $callback, $priority = 0)
    {
		$this->key = md5(date('YmdHis') . rand(0,9999));
        $this->name = $name;
        $this->callback = $callback;
        $this->priority = $priority;
    }
    public function update(SplSubject $publisher)
    {
        call_user_func($this->callback, $publisher);
    }

	public function getKey()
	{
		return $this->key;
	}

    public function getPriority()
    {
        return $this->priority;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getCallback()
    {
        return $this->callback;
    }

	public function setKey($key)
	{
		$this->key = $key;
	}

    public function setPriority($priority)
    {
        $this->priority = $priority;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function setCallback($callback)
    {
        $this->callback = $callback;
    }

}
