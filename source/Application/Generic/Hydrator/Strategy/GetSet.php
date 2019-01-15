<?php
/**
 * Hydrates based on existing getters / setters
 */
namespace Application\Generic\Hydrator\Strategy;

class GetSet implements HydratorInterface
{

	/**
	 * Typically you would define a "hydrate()" method
	 * which goes from array to object
	 * NOTE: you can only run setters where the variable name is after "set"
	 *       i.e. setFirstName() is assumed variable name == firstName
	 *
	 * @param array $array = populated array
	 * @param mixed $object = some object class
	 * @return mixed $object = object populated with data
	 */
	public static function hydrate(array $array, $object)
	{
		// get methods of this object
	    $methodList = get_class_methods(get_class($object));
		foreach ($methodList as $method) {
			preg_match('/^(set)(.*?)$/i', $method, $matches);
			$prefix = $matches[1] ?? '';
			$key    = $matches[2] ?? '';
			$key    = strtolower(substr($key, 0, 1)) . substr($key, 1);
			// only run setters
			if ($prefix == 'set' && !empty($array[$key])) {
				$object->$method($array[$key]);
			}
		}
		return $object;
	}

	/**
	 * Typically you would define an "extract()" method
	 * which goes from object to array
	 *
	 * @param mixed $object = some object class
	 * @return array $array = populated array
	 */
	public static function extract($object)
	{
	    $array = array();
		// get methods of this object
	    $methodList = get_class_methods(get_class($object));
	    foreach ($methodList as $method) {
	        preg_match('/^(get)(.*?)$/i', $method, $matches);
	        $prefix = $matches[1] ?? '';
	        $key    = $matches[2] ?? '';
	        $key    = strtolower(substr($key, 0, 1)) . substr($key, 1);
	        // only run getters
	        if ($prefix == 'get') {
	            $array[$key] = $object->$method();
	        }
	    }
	    return $array;
	}
}
