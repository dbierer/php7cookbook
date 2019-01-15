<?php
namespace Application\Generic;

use SplStack;

class Stack
{
	protected $stack;
	public function __construct()
	{
		$this->stack = new SplStack();
	}
	public function push($message)
	{
		$this->stack->push($message);
	}
	public function pop()
	{
		return $this->stack->pop();
	}
	public function __invoke()
	{
		return $this->stack;
	}
}
