<?php
namespace Application\Database;

use Exception;
use Iterator;
use PDO;

class BasicOps
{
    
    protected $connection;

    public function __construct(array $config)
    {
        $this->connection = new Connection($config);
    }

    public function select($sql, $params = NULL)
    {
        $result = FALSE;
        try {
            $stmt = $this->connection->prepare($sql);
            $result = $stmt->execute($params);
        } catch (Throwable $e) {
            error_log(__METHOD__ . ':' . $e->getMessage());
        }
        return $result;
    }
    
    public function insert($sql, Iterator $iterator)
    {
        $result = FALSE;
        try {
            $stmt = $this->connection->prepare($sql);
            foreach ($iterator as $row) {
                $stmt->execute($row);
            }
            $result = $this->connection->lastInsertId();
        } catch (Throwable $e) {
            error_log(__METHOD__ . ':' . $e->getMessage());
        }
        return $result;
    }

}
