<?php
namespace Application\Entity;

class Base
{
	// all entities which extend Base will have properties $id and $mapping
	protected $id = 0;
	protected $mapping = ['id' => 'id'];
	
	// getter and setter for ID
	public function getId() : int
	{
		return $this->id;
	}
	public function setId($id)
	{
		$this->id = (int) $id;
	}
	
	/**
	 * Populates properties of this instance from an array
	 * The reason why we pass an instance is in case we want to call this method in the constructor 
	 *
	 * @param array $data == assumes keys are database column names
	 * @param Application\Entity\* $instance
	 * @return self populated with values
	 */
	public static function arrayToEntity($data, Base $instance)
	{
		if ($data && is_array($data)) {
			foreach ($instance->mapping as $dbColumn => $propertyName) {
				$method = 'set' . ucfirst($propertyName);
				$instance->$method($data[$dbColumn]);
			}
			return $instance;
		}
		return FALSE;
	}

	/**
	 * Produces an array of data from properties of this instance
	 * 
	 * @return array $data == assumes keys are database column names
	 */
	public function entityToArray()
	{
		$data = array();
		foreach ($this->mapping as $dbColumn => $propertyName) {
			$method = 'get' . ucfirst($propertyName);
			$data[$dbColumn] = $this->$method() ?? NULL;
		}
		return $data;
	}
}
