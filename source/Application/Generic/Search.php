<?php
/**
 * Implements a search engine
 */
namespace Application\Generic;

class Search
{

	protected $primary;		// array to be searched: [key => [id,xxx,yyy], key => [id,xxx,yyy]]
	protected $iterations;

	public function __construct($primary)
	{
		$this->primary = $primary;
	}

	/**
	 * Calls doBinarySearch() and iterates through results
	 *
	 * @param array $keys = column names or numbers to include in the search
	 * @param mixed $item = what you're searching for
	 * @return Generator $results = iteration of results from $primary
	 */
	public function binarySearch(array $keys, $item)
	{
		// build an array of keys where key == $keys and value == link to $primary
		$search = array();
		foreach ($this->primary as $primaryKey => $data) {
			$searchKey = function ($keys, $data) {
				$key = '';
				foreach ($keys as $k) $key .= $data[$k];
				return $key;
			};
			$search[$searchKey($keys, $data)] = $primaryKey;
		}
		// sort by searchkey
		ksort($search);
		// produce array of keys
		$binary = array_keys($search);
		$result = $this->doBinarySearch($binary, $item);
		return $this->primary[$search[$result]] ?? FALSE;
	}

	/**
	 * Performs a binary search
	 *
	 */
	public function doBinarySearch($binary, $item)
	{
		$found = FALSE;
		$loop  = TRUE;
		$done  = -1;
		$max   = count($binary);
		$lower = 0;
		$upper = $max - 1;
		$iterations = 0;
		// loop while control is TRUE and not found
		while ($loop && !$found) {
			// is $item < or > $binary[$mid]
			$mid = (int) (($upper - $lower) / 2) + $lower;
			echo 'Upper:Mid:Lower:<=> | ' . $upper . ':' . $mid . ':' . $lower . ':' . ($item <=> $binary[$mid]) . PHP_EOL;
			// NOTE use of PHP 7 "spaceship" operator
			switch ($item <=> $binary[$mid]) {
				// $item < $binary[$mid]
				case -1 :
					$upper = $mid;
					break;
				// $item == $binary[$mid]
				case 0 :
					$found = $binary[$mid];
					break;
				// $item > $binary[$mid]
				case 1 :
				default :
					$lower = $mid;
			}
			// loop control
			$loop = (($iterations++ < $max) && ($done < 1));
			$done += ($upper == $lower) ? 1 : 0;
		}
		$this->iterations = $iterations;
		return $found;
	}

	public function getPrimary()
	{
		return $this->primary;
	}

}
