<?php
namespace Application\Generic;

use PDO;
use Application\Database\ { Connection, ConnectionTrait, ConnectionAwareInterface };

class CustomerListUsingTrait implements ConnectionAwareInterface
{

    use ListTrait;
    use ConnectionTrait;

    protected $key   = 'id';
    protected $value = 'name';
    protected $table = 'customer';
}
