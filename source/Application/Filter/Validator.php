<?php
namespace Application\Filter;

class Validator extends AbstractFilter
{
	
	/**
	 * Runs array of $data past $this->assignments (inherited)
	 * 
	 * @param array $data = $_POST data (or whatever)
	 * @return bool $valid
	 */
	public function process(array $data)
	{
		$valid = TRUE;
		// return empty array if no assignments
		if (!(isset($this->assignments) && count($this->assignments))) {
			return $valid;
		}
		// initialize $this->results
		foreach ($data as $key => $value) {
			$this->results[$key] = new Result(TRUE, array());
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
			if (!isset($data[$key])) {
				$this->results[$key] = new Result(FALSE, $this->missingMessage);
			} else {
				$this->processAssignment($assignment, $key, $data[$key]);
			}
			if (!$this->results[$key]->item) $valid = FALSE;
		}
		return $valid;
	}
	
	protected function processAssignment($assignment, $key, $value)
	{
		foreach ($assignment as $callback) {
			if ($callback === NULL) continue;
			$result = $this->callbacks[$callback['key']]($value, $callback['params']);
			$this->results[$key]->mergeValidationResults($result);
		}
	}
	
	protected function processGlobalAssignment($assignment, $data)
	{
		foreach ($assignment as $callback) {
			if ($callback === NULL) continue;
			foreach ($data as $k => $value) {
				$result = $this->callbacks[$callback['key']]($value, $callback['params']);
				$this->results[$k]->mergeValidationResults($result);
			}
		}
	}
	
}
