<?php
namespace Application\Form\Element;

use Application\Form\Generic;

class Form extends Generic
{

	public function getInputOnly()
	{
		$this->pattern = '<form name="%s" %s> ' . PHP_EOL;
		return sprintf($this->pattern, $this->name, $this->getAttribs());
	}

	public function closeTag()
	{
		return '</' . $this->type . '>';
	}
}
