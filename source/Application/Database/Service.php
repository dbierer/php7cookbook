<?php
namespace Application\Database;

class Service
{
    
    protected $connection;
    
	/**
	 * @param Application\Database\Connection $connection
	 */
    public function __construct(Connection $connection)
    {
		$this->connection = $connection;
    }

}
