<?php
namespace Application\Error;

use PDO;

class ThrowsException
{
    protected $result;
    public function __construct(array $config)
    {
        // build base DSN
        $dsn = $config['driver'] . ':';

		// look for options
		unset($config['driver']);
		foreach ($config as $key => $value) {
			$dsn .= $key . '=' . $value . ';';
		}
		$pdo = new PDO($dsn,
					   $config['user'],
					   $config['password'],
					   [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $stmt = $pdo->query('This Is Not SQL');
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $this->result[] = $row;
        }
    }
}
