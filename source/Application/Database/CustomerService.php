<?php
namespace Application\Database;

use PDO;
use PDOException;
use Application\Entity\Customer;

class CustomerService
{
    
    protected $connection;
    
	/**
	 * @param Application\Database\Connection $connection
	 */
    public function __construct(Connection $connection)
    {
		$this->connection = $connection;
    }
    
	public function fetchById($id)
	{
		$stmt = $this->connection->pdo->prepare(Finder::select('customer')->where('id = :id')::getSql());
		$stmt->execute(['id' => (int) $id]);
		
		// APPROACH #1
		// the danger of directly creating the class is that properties are injected BEFORE __construct()!!!
		// see comment by rasmus at mindplay dot dk at http://php.net/manual/en/pdostatement.fetchobject.php
		// $stmt->fetchObject('Application\Entity\Customer');

		// APPROACH #2
		// allows the constructor to run ... but fetch() is unable to overwrite protected properties.
		// for this approach to work all properties in the entity would have to be defined as public
		// $stmt->setFetchMode(PDO::FETCH_INTO, new Customer());
		// return $stmt->fetch();
		
		// APPROACH #3
		// although slightly more costly in terms of performance, allows the constructor ti run
		// and preserves protected status of properties
		return Customer::arrayToEntity($stmt->fetch(PDO::FETCH_ASSOC), new Customer());
	}

	public function fetchByLevel($level)
	{
		$stmt = $this->connection->pdo->prepare(
			Finder::select('customer')->where('level = :level')::getSql());
		$stmt->execute(['level' => $level]);
		while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			yield Customer::arrayToEntity($row, new Customer());
		}
	}

	public function fetchByEmail($email)
	{
		$stmt = $this->connection->pdo->prepare(
			Finder::select('customer')->where('email = :email')::getSql());
		$stmt->execute(['email' => $email]);
		return Customer::arrayToEntity($stmt->fetch(PDO::FETCH_ASSOC), new Customer());
	}

	public function fetchAll($limit = 20, $offset = 0)
	{
		$stmt = $this->connection->pdo->prepare(
			Finder::select('customer')->limit($limit)->offset($offset)::getSql());
		$stmt->execute();
		while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) yield $row;
	}

	public function remove(Customer $cust)
	{
		$sql = 'DELETE FROM ' . $cust::TABLE_NAME . ' WHERE id = :id';
		$stmt = $this->connection->pdo->prepare($sql);
		$stmt->execute(['id' => $cust->getId()]);
		return ($this->fetchById($cust->getId())) ? FALSE : TRUE;
	}
	
	public function save(Customer $cust)
	{
		// check to see if customer ID > 0 and exists
		if ($cust->getId() && $this->fetchById($cust->getId())) {
			return $this->doUpdate($cust);
		} else {
			return $this->doInsert($cust);
		}
	}

	protected function doUpdate($cust)
	{
		// get properties in the form of an array
		$values = $cust->entityToArray();
		// build the SQL statement
		$update = 'UPDATE ' . $cust::TABLE_NAME;
		$where = ' WHERE id = ' . $cust->getId();
		// unset ID as we want do not want this to be updated
		unset($values['id']);		
		return $this->flush($update, $values, $where);
	}
	
	protected function doInsert($cust)
	{
		// get properties in the form of an array
		$values = $cust->entityToArray();
		// save the email address for later lookup
		$email  = $cust->getEmail();
		// unset ID as we want this to be auto-generated
		unset($values['id']);
		// build the SQL statement
		$insert = 'INSERT INTO ' . $cust::TABLE_NAME . ' ';
		// perform insert
		if ($this->flush($insert, $values)) {
			// lookup new customer
			return $this->fetchByEmail($email);
		} else {
			return FALSE;
		}
	}

	protected function flush($sql, $values, $where = '')
	{
		$sql .=  ' SET ';
		foreach ($values as $column => $value) {
			$sql .= $column . ' = :' . $column . ',';
		}
		// get rid of trailing ','
		$sql     = substr($sql, 0, -1) . $where;
		$success = FALSE;
		try {
			$stmt = $this->connection->pdo->prepare($sql);
			$stmt->execute($values);
			$success = TRUE;
		} catch (PDOException $e) {
			error_log(__METHOD__ . ':' . __LINE__ . ':' . $e->getMessage());
			$success = FALSE;
		} catch (Throwable $e) {
			error_log(__METHOD__ . ':' . __LINE__ . ':' . $e->getMessage());
			$success = FALSE;
		}
		return $success;
	}
	
}
