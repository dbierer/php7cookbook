<?php
namespace Application\Filter;

class Filter extends AbstractFilter
{
	
	/**
	 * Runs array of $data past $this->assignments (inherited)
	 */
	public function process(array $data)
	{
		// return empty array if no assignments
		if (!(isset($this->assignments) && count($this->assignments))) {
			return NULL;
		}
		// initialize $this->results
		foreach ($data as $key => $value) {
			$this->results[$key] = new Result($value, array());
		}
		$toDo = $this->assignments;
		// process global assignments
		if (isset($toDo['*'])) {
			$this->processGlobalAssignment($toDo['*'], $data);
			unset($toDo['*']);
		}
		// loop through remaining assignments
		foreach ($toDo as $key => $assignment) {
			// otherwise run all callbacks for this assignment
			$this->processAssignment($assignment, $key);
		}
	}
	
	protected function processAssignment($assignment, $key)
	{
		foreach ($assignment as $callback) {
			if ($callback === NULL) continue;
			$result = $this->callbacks[$callback['key']]($this->results[$key]->item, $callback['params']);
			$this->results[$key]->mergeResults($result);
		}
	}
	
	protected function processGlobalAssignment($assignment, $data)
	{
		foreach ($assignment as $callback) {
			if ($callback === NULL) continue;
			foreach ($data as $k => $value) {
				$result = $this->callbacks[$callback['key']]($this->results[$k]->item, $callback['params']);
				$this->results[$k]->mergeResults($result);
			}
		}
	}
	
}
