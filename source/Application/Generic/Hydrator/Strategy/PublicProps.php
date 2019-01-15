<?php
/**
 * Hydrates based on public properties
 */
namespace Application\Generic\Hydrator\Strategy;

class PublicProps implements HydratorInterface
{

	/**
	 * Typically you would define a "hydrate()" method
	 * which goes from array to object
	 *
	 * @param array $array = populated array
	 * @param mixed $object = some object class
	 * @return mixed $object = object populated with data
	 */
	public static function hydrate(array $array, $object)
	{
		// get public properties of this object
	    $propertyList= array_keys(get_class_vars(get_class($object)));
		foreach ($propertyList as $property) {
			$object->$property = $array[$property] ?? NULL;
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
		// get public properties of this object
		$propertyList= array_keys(get_class_vars(get_class($object)));
		foreach ($propertyList as $property) {
			$array[$property] = $object->$property;
		}
		return $array;
	}
}
