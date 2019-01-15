<?php
namespace Application\Database;

use PDOException;
use Application\Database\Finder;

class Paginate
{
    
	const DEFAULT_LIMIT  = 20;
	const DEFAULT_OFFSET = 0;
	
	protected $sql;
	protected $page;
	protected $linesPerPage;

	/**
	 * Creates and performs the pagination
	 * 
	 * @param mixed $sql   // this would be a good place to use Finder::getSql()
	 * @param int $page = the current "page" number
	 * @param int $linesPerPage = how many lines do you want to see on the page
	 * @return string 
	 */
	public function __construct($sql, $page, $linesPerPage)
	{
		// calc offset
		$offset = $page * $linesPerPage;
		// check to see if $sql == Finder instance
		if ($sql instanceof Finder) {
			$sql->limit($linesPerPage);
			$sql->offset($offset);
			$this->sql = $sql::getSql();
		} elseif (is_string($sql)) {
			// check SQL for existing LIMIT and OFFSET
			switch (TRUE) {
				case (stripos($sql, 'LIMIT') && strpos($sql, 'OFFSET')) :
					// no action needed
					break;
				case (stripos($sql, 'LIMIT')) :
					// add LIMIT
					$sql .= ' LIMIT ' . self::DEFAULT_LIMIT;
					break;
				case (stripos($sql, 'OFFSET')) :
					// add OFFSET
					$sql .= ' OFFSET ' . self::DEFAULT_OFFSET;
					break;
				default :
					// add LIMIT
					$sql .= ' LIMIT ' . self::DEFAULT_LIMIT;
					// add OFFSET
					$sql .= ' OFFSET ' . self::DEFAULT_OFFSET;
					break;
			}
			// replace current LIMIT and OFFSET
			$this->sql = preg_replace('/LIMIT \d+.*OFFSET \d+/Ui', 
						 'LIMIT ' . $linesPerPage . ' OFFSET ' . $offset, 
						 $sql);
		}
	}
	
	/**
	 * Performs query represented by SQL
	 * 
	 * @param Application\Database\Connection $connection
	 * @param int $fetchMode (one of PDO::FETCH_???)
	 * @param array $params == params if using prepared statement
	 * @return mixed $results
	 */
	public function paginate(Connection $connection, $fetchMode, $params = array())
	{
		try {
			$stmt = $connection->pdo->prepare($this->sql);
			if (!$stmt) {
				return FALSE;
			}
			if ($params) {
				$stmt->execute($params);
			} else {
				$stmt->execute();
			}
			while ($result = $stmt->fetch($fetchMode)) {
				yield $result;
			}
		} catch (PDOException $e) {
			error_log($e->getMessage());
			return FALSE;
		} catch (Throwable $e) {
			error_log($e->getMessage());
			return FALSE;
		}
	}
	
	public function getSql()
	{
		return $this->sql;
	}
}
