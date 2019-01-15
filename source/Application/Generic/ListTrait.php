<?php
namespace Application\Generic;

use PDO;

trait ListTrait
{
    public function list()
    {
        $list = [];
        $sql  = sprintf('SELECT %s,%s FROM %s', $this->key, $this->value, $this->table);
        $stmt = $this->connection->pdo->query($sql);
        while ($item = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $list[$item[$this->key]] =  $item[$this->value];
        }
        return $list;
    }
}
