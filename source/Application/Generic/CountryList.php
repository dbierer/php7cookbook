<?php
namespace Application\Generic;

use PDO;
use Application\Database\Connection;
use Application\Database\ConnectionAwareInterface;

class CountryList implements ConnectionAwareInterface
{
    protected $connection;
    protected $key   = 'iso3';
    protected $value = 'name';
    protected $table = 'iso_country_codes';
    
    public function setConnection(Connection $connection)
    {
        $this->connection = $connection;
    }
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
