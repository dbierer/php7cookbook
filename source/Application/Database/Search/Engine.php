<?php
/**
 * Implements a search engine
 */
namespace Application\Database\Search;

use PDO;
use Application\Database\Connection;

class Engine
{

	const ERROR_PREPARE = 'ERROR: unable to prepare statement';
	const ERROR_EXECUTE = 'ERROR: unable to execute statement';
	const ERROR_COLUMN  = 'ERROR: column name not on list';
	const ERROR_OPERATOR= 'ERROR: operator not on list';
	const ERROR_INVALID = 'ERROR: invalid search criteria';

	protected $connection;
	protected $table;               // table name to search
	protected $columns;				// database columns to be searched: [column key => descriptive name of column]
	protected $mapping;				// [column key => database column name]
	protected $statement;
	protected $sql = '';
	protected $operators = [
				'LIKE' 		=> 'Equals',
				'<' 		=> 'Less Than',
				'>' 		=> 'Greater Than',
				'<>'		=> 'Not Equals',
				'NOT NULL'	=> 'Exists',
	];

	/**
	 * Initialize Engine vars
	 *
	 * @param Connection $connection
	 * @param string $table = database table to search
	 * @param array $columns = array of column names to include in search
	 * 					[column key => descriptive name of column]
	 * @param array $mappings = [column key => actual database column name]
	 */
	public function __construct(Connection $connection, $table, array $columns, array $mapping)
	{
		$this->connection  = $connection;
		$this->setTable($table);
		$this->setColumns($columns);
		$this->setMapping($mapping);
	}

	public function getOperators()
	{
		return $this->operators;
	}
	public function getSql()
	{
		return $this->sql;
	}
	public function setColumns($columns)
	{
		$this->columns = $columns;
	}
	public function getColumns()
	{
		return $this->columns;
	}
	public function setMapping($mapping)
	{
		$this->mapping = $mapping;
	}
	public function getMapping()
	{
		return $this->mapping;
	}
	public function setTable($name)
	{
		$this->table = $name;
	}
	public function getTable()
	{
		return $this->table;
	}
	/**
	 * Prepares statement
	 *
	 * @param Criteria $criteria = ['key' => key to $columns, 'item' => search item, 'operator' => = <> < > etc.]
	 */
	public function prepareStatement(Criteria $criteria)
	{
		$this->sql = 'SELECT * FROM ' . $this->table . ' WHERE ';
		$this->sql .= $this->mapping[$criteria->key] . ' ';
		switch ($criteria->operator) {
			case 'NOT NULL' :
				$this->sql .= ' IS NOT NULL OR ';
				break;
			default :
				$this->sql .= $criteria->operator . ' :' . $this->mapping[$criteria->key] . ' OR ';
		 }
		// remove trailing " OR "
		$this->sql = substr($this->sql, 0, -4)
				   . ' ORDER BY ' . $this->mapping[$criteria->key];
		$statement = $this->connection->pdo->prepare($this->sql);
		return $statement;
	}

	/**
	 * Performs search using $criteria
	 *
	 * @param Criteria $criteria = ['key' => key to $columns, 'item' => search item, 'operator' => = <> < > etc.]
	 * @return array $results
	 */
	public function search(Criteria $criteria)
	{
		if (empty($criteria->key) || empty($criteria->operator)) {
			yield ['error' => self::ERROR_INVALID];
			return FALSE;
		}
		try {
			if (!$statement = $this->prepareStatement($criteria)) {
				yield ['error' => self::ERROR_PREPARE];
				return FALSE;
			}
			$params = array();
			switch ($criteria->operator) {
				case 'NOT NULL' :
					// do nothing: already in statement
					break;
				case 'LIKE' :
					$params[$this->mapping[$criteria->key]] = '%' . $criteria->item . '%';
					break;
				default :
					$params[$this->mapping[$criteria->key]] = $criteria->item;
			}
			$statement->execute($params);
			while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
				yield $row;
			}
		} catch (Throwable $e) {
			error_log(__METHOD__ . ':' . $e->getMessage());
			throw new Exception(self::ERROR_EXECUTE);
		}
		return TRUE;
	}
}
