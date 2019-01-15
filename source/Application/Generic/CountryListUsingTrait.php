<?php
namespace Application\Generic;

use PDO;
use Application\Database\Connection;
use Application\Database\ConnectionTrait;
use Application\Database\ConnectionAwareInterface;

class CountryListUsingTrait implements ConnectionAwareInterface
{
    
    use ListTrait;
    use ConnectionTrait;
    
    protected $key   = 'iso3';
    protected $value = 'name';
    protected $table = 'iso_country_codes';
    
}
