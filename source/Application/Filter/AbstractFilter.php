<?php
namespace Application\Filter;

use UnexpectedValueException;

/**
 * Generic class which can be used to define a Filter or a Validator
 */
abstract class AbstractFilter
{

	const BAD_CALLBACK = 'Callback must implement Application\Filter\CallbackInterface';
	const DEFAULT_SEPARATOR = '<br>' . PHP_EOL;
	const MISSING_MESSAGE_KEY = 'item.missing';
	const DEFAULT_MESSAGE_FORMAT = '%20s : %60s';
	const DEFAULT_MISSING_MESSAGE = 'Item Missing';
	
	protected $separator;		// used for message display
	protected $callbacks;
	protected $assignments;
	protected $missingMessage;
	
	/**
	 * Array in the following format:
	 * ['key1' => Application\Filter\Result $obj,
	 *  'key2' => Application\Filter\Result $obj,
	 *  etc. ]
	 */
	protected $results = array();
	
	/**
	 * Builds Filter or Validator instance
	 * 
	 * @param array $callbacks = Array in the following format:
	 * 'callKey' => class implements CallbackInterface { __invoke ($item, array $params) { return Result $obj } }
	 * @param array $assignments['filters'|'validators'] = Array in the following format:
	 * 'postFieldKey' => [
	 * 		[ 'key' => callback key, 'params' => array $params ],
	 * 		[ 'key' => callback key, 'params' => array $params ],
	 * 		etc.
	 *  ]
	 * @param string $separator
	 * @param string $message = item missing message
	 */
	public function __construct(array $callbacks, array $assignments, $separator = NULL, $message = NULL)
	{
		$this->setCallbacks($callbacks);
		$this->setAssignments($assignments);
		$this->setSeparator($separator ?? self::DEFAULT_SEPARATOR);
		$this->setMissingMessage($message ?? self::DEFAULT_MISSING_MESSAGE);
	}

	// ********* Callback Processing ********* //
	
	public function getCallbacks()
	{
		return $this->callbacks;
	}

	public function getOneCallback($key)
	{
		return $this->callbacks[$key] ?? NULL;
	}

	/**
	 * Builds array of callbacks
	 * 
	 * @param array $callbacks
	 * @throws UnexpectedValueException if one of the callbacks doesn't implement CallbackInterface
	 */
	public function setCallbacks(array $callbacks)
	{
		foreach ($callbacks as $key => $item) {
			$this->setOneCallback($key, $item);
		}
	}

	public function setOneCallback($key, $item)
	{
		if ($item instanceof CallbackInterface) {
			$this->callbacks[$key] = $item;
		} else {
			throw new UnexpectedValueException(self::BAD_CALLBACK);
		}
	}

	public function removeOneCallback($key)
	{
		if (isset($this->callbacks[$key])) unset($this->callbacks[$key]);
	}

	// ********* Results Processing ********* //
	
	public function getResults()
	{
		return $this->results;
	}

	public function getItemsAsArray()
	{
		$return = array();
		if ($this->results) {
			foreach ($this->results as $key => $item) 
				$return[$key] = $item->item;
		}
		return $return;
	}
	
	// ********* Message Processing ********* //
	
	public function getMessages()
	{
		if ($this->results) {
			foreach ($this->results as $key => $item) 
				// PHP 7 delegating generator
				if ($item->messages) yield from $item->messages;
		} else {
			return array();
		}
	}

	public function getMessagesAsArray()
	{
		$messages = array();
		if ($this->results) {
			foreach ($this->results as $key => $item) 
				if ($item->messages) $messages[$key] = $item->messages;
		}
		return $messages;
	}

	public function getMessageString($width = 80, $format = NULL)
	{
		if (!$format) $format = self::DEFAULT_MESSAGE_FORMAT . $this->separator;
		$output = '';
		if ($this->results) {
			foreach ($this->results as $key => $value) {
				if ($value->messages) {
					foreach ($value->messages as $message) {
						$output .= sprintf($format, $key, trim($message));
					}
				}
			}
		}
		return $output;
	}
	
	// ********* Misc getters and setters ********* //
	
	public function setMissingMessage($message)
	{
		$this->missingMessage = $message;
	}

	public function setSeparator($separator)
	{
		$this->separator = $separator;
	}

	public function getSeparator()
	{
		return $this->separator;
	}

	public function getAssignments()
	{
		return $this->assignments;
	}

	public function setAssignments(array $assignments)
	{
		$this->assignments = $assignments;
	}
	
}
