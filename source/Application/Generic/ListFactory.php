<?php
namespace Application\Generic;

use PDO;
use Exception;
use Application\Database\Connection;
use Application\Database\ConnectionAwareInterface;

class ListFactory
{
    public static function factory(ConnectionAwareInterface $class, $dbParams)
    {
        if ($class instanceof ConnectionAwareInterface) {
            // set up database connection
            $class->setConnection(new Connection($dbParams));
            return $class;
        } else {
            throw new Exception('Unable to initialize this class.  Must be Connection Aware');
        }
        return FALSE;
    }
}
