<?php
namespace Application\Filter;

class Result
{
	
	public $item;				// (mixed) filtered data | (bool) result of validation
	public $messages = array();	// [(string) message, (string) message ]
	
	
	public function __construct($item, $messages)
	{
		$this->item = $item;
		if (is_array($messages)) {
			$this->messages = $messages;
		} else {
			$this->messages = [$messages];
		}
	}
	
	/**
	 * Merges this with another Result object
	 * $result->item overrides $this->item
	 * 
	 * @param Application\Filter\Result $result
	 * @return Application\Filter\Result $result
	 */
	public function mergeResults(Result $result)
	{
		$this->item = $result->item;
		$this->mergeMessages($result);
	}

	/**
	 * Merges this with another Result object
	 * any FALSE will override any TRUE setting
	 * 
	 * @param Application\Filter\Result $result
	 * @return Application\Filter\Result $result
	 */
	public function mergeValidationResults(Result $result)
	{
		if ($this->item === TRUE) {
			$this->item = (bool) $result->item;
		}
		$this->mergeMessages($result);
	}

	/**
	 * Merges messages with another Result object
	 * 
	 * @param Application\Filter\Result $result
	 * @return Application\Filter\Result $result
	 */
	public function mergeMessages(Result $result)
	{
		if (isset($result->messages) && is_array($result->messages)) {
			$this->messages = array_merge($this->messages, $result->messages);
		} else {
			$this->messages = $result->messages;
		}
	}

}
