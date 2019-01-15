<?php
namespace Application\Form\Element;

use Application\Form\Generic;

class CheckBox extends Radio
{

	protected $selectedKeys = [DEFAULT_OPTION_KEY];
	
	/**
	 * Assigns options to array of checkboxes
	 * 
	 * @param array $options = key => value pairs where key = "value" attrib and value = what appears next to box
	 * @param array $selectedKeys = [string | int => default value | selected value(s)]
	 * @param string $spacer = separator between box and text
	 * @param boolean $after = text appears after box (otherwise before)
	 */
	public function setOptions(array $options, 
								$selectedKeys = [self::DEFAULT_OPTION_KEY], 
								$spacer = self::DEFAULT_SPACER,
								$after  = TRUE)
	{
		$this->after = $after;
		$this->spacer = $spacer;
		$this->options = $options;
		$this->selectedKeys = $selectedKeys;
	}
	
	public function getInputOnly()
	{
		// store ID and combine with $count to produce unique IDs
		$count  = 1;
		$baseId = $this->attributes['id'];
		$name   = $this->name;
		// add remaining boxs to output
		foreach ($this->options as $key => $value) {
			$this->name = $name . '[' . $count . ']';		
			$this->attributes['id'] = $baseId . $count++;
			$this->attributes['value'] = $key;
			if (in_array($key, $this->selectedKeys)) {
				$this->attributes['checked'] = $key;
			} else {
				if (isset($this->attributes['checked'])) unset($this->attributes['checked']);
			}
			if ($this->after) {
				$html = Generic::getInputOnly() . $value;
			} else {
				$html = $value . Generic::getInputOnly();
			}
			$output .= $this->spacer . $html;
		}
		return substr($output, strlen($this->spacer));
	}

}
