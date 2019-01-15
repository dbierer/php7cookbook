<?php
define('CURRENCY_CSV', __DIR__ . '/source/data/files/iso_country_codes.csv');
define('DB_CONFIG_FILE', '/source/config/db.config.php');

// setup class autoloading
require __DIR__ . '/source/Application/Autoload/Loader.php';

// add current directory to the path
Application\Autoload\Loader::init(__DIR__ . '/source');

// classes to use
use Application\Database\Connection;

$connection = new Connection(include __DIR__ . DB_CONFIG_FILE);
$pdo = $connection->pdo;

// erase current file
file_put_contents(CURRENCY_CSV, '');

$fileObj = new SplFileObject(CURRENCY_CSV, 'w');
$stmt = $pdo->query('SELECT * FROM iso_country_codes');
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
	$fileObj->fputcsv($row);
}
unset($fileObj);
readfile(CURRENCY_CSV);
