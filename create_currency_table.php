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
while ($csvRow = $fileObj->fgetcsv()) {
	$search = substr($csvRow[0], 0, 8) . '%';
	$stmt->execute([$search]);
	$dbRow = $stmt->fetch(PDO::FETCH_ASSOC);
	if ($dbRow) {
		$currencyTable[$dbRow['iso2']] = [
			'countryName' => $dbRow['name'], 
			'currencyName' => $csvRow[1], 
			'currencyCode' => $csvRow[2], 
			'currencyNumber' => $csvRow[3],
		];
	} else {
		$currencyTable[substr($csvRow[0], 0, 2)] = [
			'countryName' => ucwords(strtolower($csvRow[0])),
			'currencyName' => $csvRow[1], 
			'currencyCode' => $csvRow[2], 
			'currencyNumber' => $csvRow[3],
		];
	}
}
echo serialize($currencyTable);
