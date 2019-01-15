<?php
/**
 * Implements a search engine
 */
namespace Application\Database\Search;

class Criteria
{
	public $key;
	public $item;
	public $operator;
	public function __construct($key, $operator, $item = NULL)
	{
		$this->key  = $key;
		$this->operator = $operator;
		$this->item = $item;
	}
}
