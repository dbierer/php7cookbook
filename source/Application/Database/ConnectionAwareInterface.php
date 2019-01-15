<?php
namespace Application\Database;

use Application\Database\Connection;

interface ConnectionAwareInterface
{
    public function setConnection(Connection $connection);
}
