<?php
/**
 * Hydrates by creating a new class which extends the original class
 * and defining magic getters / setters for access to properties
 */
namespace Application\Generic\Hydrator\Strategy;

class Extending implements HydratorInterface
{

	const UNDEFINED_PREFIX = 'undefined';
	const TEMP_SUFFIX = '_TEMP';
	const ERROR_EVAL = 'ERROR: unable to evaluate object';

	/**
	 * Typically you would define a "hydrate()" method
	 * which goes from array to object
	 *
	 * @param array $array = populated array
	 * @param mixed $object = some object class
	 * @return mixed $populated = object of same class populated with data
	 */
	public static function hydrate(array $array, $object)
	{
		$className = get_class($object);
		$components = explode('\\', $className);
		$realClass  = array_pop($components);
		$nameSpace  = implode('\\', $components);
		$tempClass = $realClass . self::TEMP_SUFFIX;
		$template = 'namespace ' . $nameSpace . '{'
				  . 'class ' . $tempClass . ' extends ' . $realClass . ' '
				  . '{ '
				  . '  protected $values; '
				  . '  public function __construct($array) '
				  . '  { $this->values = $array; '
				  . '    foreach ($array as $key => $value) '
				  . '       $this->$key = $value; '
				  . '  } '
				  . '  public function getArrayCopy() '
				  . '  { return $this->values; } '
				  . '  public function __get($key) '
				  . '  { return $this->values[$key] ?? NULL; } '
				  . '  public function __call($method, $params) '
				  . '  { '
				  . '    preg_match("/^(get|set)(.*?)$/i", $method, $matches); '
				  . '    $prefix = $matches[1] ?? ""; '
				  . '    $key    = $matches[2] ?? ""; '
				  . '    $key    = strtolower(substr($key, 0, 1)) . substr($key, 1); '
				  . '    if ($prefix == "get") { return $this->values[$key] ?? NULL; } '
				  . '    else { $this->values[$key] = $params[0]; } '
				  . '  } '
				  . '} '
				  . '} // ends namespace ' . PHP_EOL
				  . 'namespace { '
				  . 'function build($array) '
				  . '{ return new ' . $nameSpace . '\\' . $tempClass . '($array); } '
				  . '} // ends global namespace '
				  . PHP_EOL;
		try {
			eval($template);
		} catch (ParseError $e) {
			error_log(__METHOD__ . ':' . $e->getMessage());
			throw new Exception(self::ERROR_EVAL);
		}
		return \build($array);
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
	    $class = get_class($object);
		// get methods of this object
	    $methodList = get_class_methods($class);
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
		// get public properties of this object
		$propertyList= array_keys(get_class_vars($class));
		foreach ($propertyList as $property) {
			$array[$property] = $object->$property;
		}
	    return $array;
	}
}
