<?php
namespace Application\Database;

trait ConnectionTrait
{
    protected $connection;
    public function setConnection(Connection $connection)
    {
        $this->connection = $connection;
    }
}
