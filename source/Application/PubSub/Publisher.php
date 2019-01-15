<?php
namespace Application\PubSub;

/**
 * NOTE: this actually implements Subject/Observer, not PubSub!!!
 */
use SplSubject;
use SplObserver;

class Publisher implements SplSubject
{

    protected $name;
    protected $data;
    protected $linked;		// linked list
    protected $subscribers;

    public function __construct($name)
    {
        $this->name = $name;
        $this->data = array();
        $this->linked = array();
        $this->subscribers = array();
    }

    public function __toString()
    {
        return $this->name;
    }

    public function attach(SplObserver $subscriber)
    {
		$this->subscribers[$subscriber->getKey()] = $subscriber;
		$this->linked[$subscriber->getKey()] = $subscriber->getPriority();
		arsort($this->linked);
    }

    public function detach(SplObserver $subscriber)
    {
		unset($this->subscribers[$subscriber->getKey()]);
		unset($this->linked[$subscriber->getKey()]);
    }

    public function notify()
    {
        foreach ($this->linked as $key => $value)
        {
            $this->subscribers[$key]->update($this);
        }
    }

    public function getName()
    {
        return $this->name;
    }

    public function getData()
    {
        return $this->data;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function setData($data)
    {
        $this->data = $data;
    }

    public function setDataByKey($key, $value)
    {
        $this->data[$key] = $value;
    }

}
