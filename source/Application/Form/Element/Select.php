<?php
namespace Application\Form\Element;

use Application\Form\Generic;

class Select extends Generic
{

	const DEFAULT_OPTION_KEY = 0;
	const DEFAULT_OPTION_VALUE = 'Choose';

	protected $options;
	protected $selectedKey = DEFAULT_OPTION_KEY;
	
	/**
	 * Assigns options to array of radio buttons
	 * 
	 * @param array $options = key => value pairs where key = "value" attrib and value = what appears next to button
	 * @param string | array = $selectedKey = default value | selected value
	 * @param string $spacer = separator between button and text
	 * @param boolean $after = text appears after button (otherwise before)
	 */
	public function setOptions(array $options, $selectedKey = self::DEFAULT_OPTION_KEY)
	{
		$this->options = $options;
		$this->selectedKey = $selectedKey;
		if (isset($this->attributes['multiple'])) {
			$this->name .= '[]';
		} 
	}
	
	public function getInputOnly()
	{
		// check for multi-select
		$output = $this->getSelect();
		$output .= $this->getOptions();
		$output .= '</' . $this->getType() . '>'; 
		return $output;
	}

	protected function getSelect()
	{
		$this->pattern = '<select name="%s" %s> ' . PHP_EOL;
		return sprintf($this->pattern, $this->name, $this->getAttribs());
	}

	protected function getOptions()
	{
		$output = '';
		foreach ($this->options as $key => $value) {
			if (is_array($this->selectedKey)) {
				$selected = (in_array($key, $this->selectedKey)) ? ' selected' : '';
			} else {
				$selected = ($key == $this->selectedKey) ? ' selected' : '';
			}
			$output .= '<option value="' . $key . '"' . $selected  . '>' 
					 . $value 
					 . '</option>' . PHP_EOL;
		}
		return $output;
	}

}
