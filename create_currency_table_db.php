<?php
define('CURRENCY_CSV', __DIR__ . '/source/data/files/currencies.csv');
define('DB_CONFIG_FILE', '/source/config/db.config.php');

// setup class autoloading
require __DIR__ . '/source/Application/Autoload/Loader.php';

// add current directory to the path
Application\Autoload\Loader::init(__DIR__ . '/source');

// classes to use
use Application\Database\Connection;

$connection = new Connection(include __DIR__ . DB_CONFIG_FILE);
$pdo = $connection->pdo;

$fileObj = new SplFileObject(CURRENCY_CSV, 'r');
$stmt = $pdo->prepare('SELECT * FROM iso_country_codes WHERE name LIKE ?');
$stmt2 = $pdo->prepare('UPDATE iso_country_codes SET currency_name = ?, currency_code = ?, currency_number = ? WHERE iso2 = ?');
while ($csvRow = $fileObj->fgetcsv()) {
	$search = ucwords(strtolower(substr($csvRow[0], 0, 12))) . '%';
	echo $search .  PHP_EOL;
	$stmt->execute([$search]);
	$dbRow = $stmt->fetch(PDO::FETCH_ASSOC);
	if ($dbRow) {
		$stmt2->execute([$csvRow[1], $csvRow[2], $csvRow[3], $dbRow['iso2']]);
	}
}
echo serialize($currencyTable);
