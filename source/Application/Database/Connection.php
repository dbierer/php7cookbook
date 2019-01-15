<?php
namespace Application\Database;

use Exception;
use PDO;

class Connection
{
    
    const ERROR_UNABLE = 'ERROR: Unable to create database connection';
     
    public $pdo;

	/**
	 * Creates PDO connection
	 * 
	 * @param array $config = key/value pairs which are used to build the DSN
	 *                        i.e. ['host' => $host, 'dbname' => $dbname, etc.]
	 * @return TRUE
	 */
    public function __construct(array $config)
    {
		// make sure driver is set
        if (!isset($config['driver'])) {
            $message = __METHOD__ . ' : ' . self::ERROR_UNABLE . PHP_EOL;
            throw new Exception($message);
        }
        
        // build DSN
        $dsn = $this->makeDsn($config);
        
        try {
            $this->pdo = new PDO($dsn, 
                                 $config['user'], 
                                 $config['password'], 
                                 [PDO::ATTR_ERRMODE => $config['errmode']]);
			return TRUE;
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return FALSE;
        }
    }

    public static function factory($driver, $dbname, $host, 
                                   $user, $pwd, array $options = array())
    {
        // build DSN
        $dsn = $this->makeDsn($config);
        
        try {
            return new PDO($dsn, $user, $pwd, $options);
        } catch (PDOException $e) {
            error_log($e->getMessage);
        }
    }

	public function makeDsn($config)
	{
        // build base DSN
        $dsn = $config['driver'] . ':';
        
		// look for options
		unset($config['driver']);
		foreach ($config as $key => $value) {
			$dsn .= $key . '=' . $value . ';';
		}
		
		// return DSN minus last ";"
		return substr($dsn, 0, -1);
	}
	
}
