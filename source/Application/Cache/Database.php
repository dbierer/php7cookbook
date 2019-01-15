<?php
/**
 * Implements cache
 *
 * Methods:
 * hasKey($key)
 * getFromCache($key, $group = Constants::DEFAULT_GROUP)
 * saveToCache($key, $data, $group = Constants::DEFAULT_GROUP)
 * removeByKey($key)
 * removeByGroup($group)
 *
 */
namespace Application\Cache;

use PDO;
use Application\Database\Connection;

class Database implements CacheAdapterInterface
{
    protected $sql;
	protected $connection;
	protected $table;
	protected $dataColumnName;
	protected $keyColumnName;
	protected $groupColumnName;
    protected $statementHasKey       = NULL;
	protected $statementGetFromCache = NULL;
	protected $statementSaveToCache  = NULL;
	protected $statementRemoveByKey  = NULL;
	protected $statementRemoveByGroup= NULL;

	/**
	 * Initialize Cache vars
	 *
	 * @param Connection $connection
	 * @param string $table = database table to search
	 * @param string $idColumnName = name of the primary key column
	 * @param string $keyColumnName = name of the column which holds the cache key
	 * @param string $dataColumnName = name of the column which holds cache data
	 * @param string $groupColumnName = [optional] tag to group cache data together
	 */
	public function __construct(Connection $connection,
                                $table,
                                $idColumnName,
                                $keyColumnName,
                                $dataColumnName,
                                $groupColumnName = Constants::DEFAULT_GROUP)
	{
		$this->connection  = $connection;
		$this->setTable($table);
        $this->setIdColumnName($idColumnName);
		$this->setDataColumnName($dataColumnName);
        $this->setKeyColumnName($keyColumnName);
        $this->setGroupColumnName($groupColumnName);
	}

	public function prepareHasKey()
	{
		$sql = 'SELECT `' . $this->idColumnName . '` '
		     . 'FROM `'   . $this->table . '` '
		     . 'WHERE `'  . $this->keyColumnName . '` = :key ';
        $this->sql[__METHOD__] = $sql;
		$this->statementHasKey = $this->connection->pdo->prepare($sql);
	}

	public function prepareGetFromCache()
	{
		$sql = 'SELECT `' . $this->dataColumnName . '` '
		     . 'FROM `'   . $this->table . '` '
		     . 'WHERE `'  . $this->keyColumnName . '` = :key '
		     . 'AND `'    . $this->groupColumnName . '` = :group';
        $this->sql[__METHOD__] = $sql;
		$this->statementGetFromCache = $this->connection->pdo->prepare($sql);
	}

	public function prepareSaveToCache()
	{
		$sql = $this->getSet('INSERT INTO ');
        $this->sql[__METHOD__] = $sql;
		$this->statementSaveToCache = $this->connection->pdo->prepare($sql);
	}

	public function prepareUpdateCache()
	{
		$sql = $this->getSet('UPDATE ')
             . ' WHERE `' . $this->idColumnName . '` = :id';
        $this->sql[__METHOD__] = $sql;
		$this->statementUpdateCache = $this->connection->pdo->prepare($sql);
	}

    protected function getSet($prefix)
    {
        return $prefix  . '`' . $this->table . '`' . ' SET `'
			 . $this->keyColumnName . '` = :key' . ',`'
			 . $this->dataColumnName . '` = :data' . ',`'
		     . $this->groupColumnName . '` = :group';
    }

	public function prepareRemoveByKey()
	{
		$sql = 'DELETE FROM `' . $this->table . '` '
		     . 'WHERE `'  . $this->keyColumnName . '` = :key ';
        $this->sql[__METHOD__] = $sql;
		$this->statementRemoveByKey = $this->connection->pdo->prepare($sql);
	}

	public function prepareRemoveByGroup()
	{
		$sql = 'DELETE FROM `' . $this->table . '` '
		     . 'WHERE `'  . $this->groupColumnName . '` = :group ';
        $this->sql[__METHOD__] = $sql;
		$this->statementRemoveByGroup = $this->connection->pdo->prepare($sql);
	}

    /**
     * Returns the ID of the key column or 0
     *
     * @param string $key
     * @return int $id | 0
     */
	public function hasKey($key)
	{
		$result = 0;
		try {
            if (!$this->statementHasKey) $this->prepareHasKey();
			$this->statementHasKey->execute(['key' => $key]);
		} catch (Throwable $e) {
			error_log(__METHOD__ . ':' . $e->getMessage());
			throw new Exception(Constants::ERROR_REMOVE_KEY);
		}
		return (int) $this->statementHasKey->fetch(PDO::FETCH_ASSOC)[$this->idColumnName];
	}

	public function getFromCache($key, $group = Constants::DEFAULT_GROUP)
	{
		try {
            if (!$this->statementGetFromCache) $this->prepareGetFromCache();
			$this->statementGetFromCache->execute(['key' => $key, 'group' => $group]);
			while ($row = $this->statementGetFromCache->fetch(PDO::FETCH_ASSOC)) {
                if ($row && count($row)) {
                    yield unserialize($row[$this->dataColumnName]);
                }
			}
		} catch (Throwable $e) {
			error_log(__METHOD__ . ':' . $e->getMessage());
			throw new Exception(Constants::ERROR_GET);
		}
	}

	public function saveToCache($key, $data, $group = Constants::DEFAULT_GROUP)
	{
        $id = $this->hasKey($key);
    	$result = 0;
		try {
            if ($id) {
                if (!$this->statementUpdateCache) $this->prepareUpdateCache();
                $result = $this->statementUpdateCache->execute(['key' => $key, 'data' => serialize($data), 'group' => $group, 'id' => $id]);
            } else {
                if (!$this->statementSaveToCache) $this->prepareSaveToCache();
                $result = $this->statementSaveToCache->execute(['key' => $key, 'data' => serialize($data), 'group' => $group]);
            }
		} catch (Throwable $e) {
			error_log(__METHOD__ . ':' . $e->getMessage());
			throw new Exception(Constants::ERROR_SAVE);
		}
		return $result;
	}

	public function removeByKey($key)
	{
		$result = 0;
		try {
            if (!$this->statementRemoveByKey) $this->prepareRemoveByKey();
			$result = $this->statementRemoveByKey->execute(['key' => $key]);
		} catch (Throwable $e) {
			error_log(__METHOD__ . ':' . $e->getMessage());
			throw new Exception(Constants::ERROR_REMOVE_KEY);
		}
		return $result;
	}

	public function removeByGroup($group)
	{
 		$result = 0;
		try {
            if (!$this->statementRemoveByGroup) $this->prepareRemoveByGroup();
			$result = $this->statementRemoveByGroup->execute(['group' => $group]);
		} catch (Throwable $e) {
			error_log(__METHOD__ . ':' . $e->getMessage());
			throw new Exception(Constants::ERROR_REMOVE_GROUP);
		}
		return $result;
	}

    public function getSql()
    {
        return $this->sql;
    }

	public function setTable($name)
	{
		$this->table = $name;
	}
	public function getTable()
	{
		return $this->table;
	}
	public function setIdColumnName($name)
	{
		$this->idColumnName = $name;
	}
	public function getIdColumnName()
	{
		return $this->idColumnName;
	}
	public function setKeyColumnName($name)
	{
		$this->keyColumnName = $name;
	}
	public function getKeyColumnName()
	{
		return $this->keyColumnName;
	}
	public function setDataColumnName($name)
	{
		$this->dataColumnName = $name;
	}
	public function getDataColumnName()
	{
		return $this->dataColumnName;
	}
	public function setGroupColumnName($name)
	{
		$this->groupColumnName = $name;
	}
	public function getGroupColumnName()
	{
		return $this->groupColumnName;
	}
}
