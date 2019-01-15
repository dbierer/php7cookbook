<?php
namespace Application\Test;

use PDO;
use Exception;
use DateTime;
use DateInterval;
use PDOException;
use SplFileObject;
use InvalidArgumentsException;
use Application\Database\Connection;

class FakeData
{

	const MAX_LOOKUPS  = 10;	// max # times we will try to lookup data in the source table

	const SOURCE_FILE  = 'file';
	const SOURCE_TABLE = 'table';
	const SOURCE_METHOD = 'method';
	const SOURCE_CALLBACK = 'callback';

	const FILE_TYPE_CSV = 'csv';
	const FILE_TYPE_TXT = 'txt';

	const ERROR_DB     = 'ERROR: unable to read source table';
	const ERROR_LOOKUP = 'ERROR: find any IDs in the source table; make sure primary key is an int in order (i.e. 1,2,3,4,5 etc.)';
	const ERROR_FILE   = 'ERROR: file not found';
	const ERROR_UPLOAD = 'ERROR: unable to upload file';
	const ERROR_COUNT  = 'ERROR: unable to ascertain count or ID column missing';

	protected $connection;
	protected $mapping;
	protected $files;
	protected $tables;
	protected $alpha = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
	protected $street1 = ['Amber','Blue','Bright','Broad','Burning','Cinder','Clear','Colonial','Cotton','Cozy','Crystal',
						  'Dewy','Dusty','Easy','Emerald','Fallen','Foggy','Gentle','Golden','Grand','Green',
						  'Harvest','Hazy','Heather','Hidden','High','Honey','Indian','Iron','Jagged','Lazy','Little','Lost',
						  'Merry','Middle','Misty','Noble','Old','Pleasant','Quaking','Quiet','Red','Rocky','Round','Rustic',
						  'Shady','Silent','Silver','Sleepy','Stony','Sunny','Tawny','Thunder','Umber','Velvet','Wishing',''];
	protected $street2 = ['Anchor','Apple','Autumn','Barn','Beacon','Bear','Berry','Blossom','Bluff','Branch','Brook','Butterfly',
						  'Cider','Cloud','Creek','Dale','Deer','Elk','Embers','Fawn','Forest','Fox','Gate','Goose','Grove',
						  'Hickory','Hills','Horse','Island','Lagoon','Lake','Leaf','Log','Mountain','Nectar','Oak','Panda','Pine',
						  'Pioneer','Pond','Pony','Prairie','Quail','Rabbit','Rise','River','Robin','Shadow','Sky','Spring',
						  'Timber','Treasure','View','Wagon ','Willow','Zephyr',''];
	protected $street3 = ['Acres','Arbor','Avenue','Bank','Bend','Canyon','Chase','Circle','Corner','Court','Cove','Crest',
						  'Dale','Dell','Edge','Estates','Falls','Farms','Gardens','Gate','Glade','Glen','Grove',
						  'Highlands','Hollow','Isle','Jetty','Knoll','Landing','Lane','Ledge','Manor','Meadow','Mews','Nook',
						  'Orchard','Park','Path','Pike','Place','Point','Promenade','Ridge','Round','Run','Stead','Swale',
						  'Terrace','Trace','Trail','Vale','Valley','View','Vista','Way','Woods','Boulevard','Street','Lane','Drive'];
	protected $email1 = ['northern','southern','eastern','western','fast','midland','central'];
	protected $email2 = ['telecom','telco','net','connect'];
	protected $email3 = ['com','net'];

	/**
	 *
	 * @param Connection $conn = PDO connection
	 * @param array $mapping = mapping information between source and destination
	 */
	public function __construct(Connection $conn,
								array $mapping,
								$destTableName)
	{
		$this->connection = $conn;
		$this->mapping = $mapping;
	}

	/**
	 * Uploads contents of $file to $table
	 *
	 * @param string $file = source CSV filename
	 * @param string $table = table name
	 * @param array $columns = array of database column names; must be in order according to $pattern
	 */
	public function uploadFileToDest($file, $table, $columns)
	{
		if (!file_exists($file)) {
			throw new InvalidArgumentsException(self::ERROR_FILE);
		}
		$sql = 'INSERT INTO ' . $table
			 . ' (' . implode(',', $columns) . ') '
			 . ' VALUES '
			 . ' (?' . str_repeat(',?', count($columns) - 1) . ') ';
		echo $sql . PHP_EOL;
		try {
			$stmt = $this->connection->pdo->prepare($sql);
			$fileObj = new SplFileObject($file, 'r');
			while ($data = $fileObj->fgetcsv()) {
				echo 'Processing: ' . $data[0] . ':' . $data[1] . ':' . $data[2] . PHP_EOL;
				$stmt->execute($data);
			}
		} catch (PDOException $e) {
			error_log(__METHOD__ . ':' . $e->getMessage());
			throw new Exception(self::ERROR_UPLOAD);
		}
		return TRUE;
	}

	public function generateData($howMany, $destTableName = NULL, $truncateDestTable = FALSE)
	{
		try {
			if ($destTableName) {
				$sql = 'INSERT INTO ' . $destTableName
					 . ' (' . implode(',', array_keys($this->mapping)) . ') '
					 . ' VALUES '
					 . ' (:' . implode(',:', array_keys($this->mapping)) . ')';
				$stmt = $this->connection->pdo->prepare($sql);
				if ($truncateDestTable) {
					$sql    = 'DELETE FROM ' . $destTableName;
					$this->connection->pdo->query($sql);
				}
			}
		} catch (PDOException $e) {
			error_log(__METHOD__ . ':' . $e->getMessage());
			throw new Exception(self::ERROR_COUNT);
		}
		for ($x = 0; $x < $howMany; $x++) {
			$entry = $this->getRandomEntry();
			if ($insert) {
				try {
					$stmt->execute($entry);
				} catch (PDOException $e) {
					error_log(__METHOD__ . ':' . $e->getMessage());
					throw new Exception(self::ERROR_DB);
				}
			}
			yield $entry;
		}
	}

	/**
	 * Generates a single random entry
	 * NOTE: the $mapping['source'] parameter effectively implements the Strategy Pattern
	 *
	 * @return array $entry
	 */
	public function getRandomEntry()
	{
		$entry = array();
		foreach ($this->mapping as $key => $value) {
			if (isset($value['source'])) {
				switch ($value['source']) {
					case self::SOURCE_FILE :
						$entry[$key] = $this->getEntryFromFile($value['name'], $value['type']);
						break;
					case self::SOURCE_CALLBACK :
						$entry[$key] = $value['name']();
						break;
					case self::SOURCE_TABLE :
						$result = $this->getEntryFromTable($value['name'],$value['idCol'],$value['mapping']);
						$entry = array_merge($entry, $result);
						break;
					case self::SOURCE_METHOD :
					default :
						if (!empty($value['params'])) {
							// NOTE: this is one effect of AST on syntax
							// in PHP 5 you could leave it as this:
							// $entry[$key] = $this->$value['name']($value['params']);
							// BUT ... in PHP 7 this is interpolated as follows:
							// $entry[$key] = {$this->$value}['name']($value['params']);
							// so we need to add {} to inform the interpreter of the desired
							$entry[$key] = $this->{$value['name']}($entry, $value['params']);
						} else {
							$entry[$key] = $this->{$value['name']}($entry);
						}
				}
			}
		}
		return $entry;
	}

	public function getEntryFromFile($name, $type)
	{
		if (empty($this->files[$name])) {
			$this->pullFileData($name, $type);
		}
		return $this->files[$name][random_int(0, count($this->files[$name]))];
	}

	public function pullFileData($name, $type)
	{
		if (!file_exists($name)) {
			throw new Exception(self::ERROR_FILE);
		}
		$fileObj = new SplFileObject($name, 'r');
		if ($type == self::FILE_TYPE_CSV) {
			while ($data = $fileObj->fgetcsv()) {
				$this->files[$name][] = trim($data);
			}
		} else {
			while ($data = $fileObj->fgets()) {
				$this->files[$name][] = trim($data);
			}
		}
	}

	public function getEntryFromTable($tableName, $idColumn, $mapping)
	{
		$entry = array();
		try {
			if (empty($this->tables[$tableName])) {
				// get 1st ID
				$sql  = 'SELECT ' . $idColumn . ' FROM ' . $tableName . ' ORDER BY ' . $idColumn . ' ASC LIMIT 1';
				$stmt = $this->connection->pdo->query($sql);
				$this->tables[$tableName]['first'] = $stmt->fetchColumn();
				// get last ID
				$sql  = 'SELECT ' . $idColumn . ' FROM ' . $tableName . ' ORDER BY ' . $idColumn . ' DESC LIMIT 1';
				$stmt = $this->connection->pdo->query($sql);
				$this->tables[$tableName]['last'] = $stmt->fetchColumn();
			}
			$result = FALSE;
			$count = self::MAX_LOOKUPS;
			$sql  = 'SELECT * FROM ' . $tableName . ' WHERE ' . $idColumn . ' = ?';
			$stmt = $this->connection->pdo->prepare($sql);
			do {
				$id = random_int($this->tables[$tableName]['first'], $this->tables[$tableName]['last']);
				$stmt->execute([$id]);
				$result = $stmt->fetch(PDO::FETCH_ASSOC);
			} while ($count-- && !$result);
			if (!$result) {
				error_log(__METHOD__ . ':' . self::ERROR_LOOKUP);
				throw new Exception(self::ERROR_LOOKUP);
			}
		} catch (PDOException $e) {
			error_log(__METHOD__ . ':' . $e->getMessage());
			throw new Exception(self::ERROR_DB);
		}
		// map return lookup result to dest column mappings
		foreach ($mapping as $key => $value) {
			$entry[$value] = $result[$key] ?? NULL;
		}
		return $entry;
	}

	public function getAddress($entry)
	{
		return random_int(1,999)
			. ' ' . $this->street1[array_rand($this->street1)]
			. ' ' . $this->street2[array_rand($this->street2)]
			. ' ' . $this->street3[array_rand($this->street3)];
	}

	public function getEmail($entry, $params = NULL)
	{
		$first = $entry[$params[0]] ?? $this->alpha[random_int(0,25)];
		$last  = $entry[$params[1]] ?? $this->alpha[random_int(0,25)];
		return $first[0] . '.' . $last
			   . '@'
			   . $this->email1[array_rand($this->email1)]
			   . $this->email2[array_rand($this->email2)]
			   . '.'
			   . $this->email3[array_rand($this->email3)];
	}

	/**
	 * Generates a date which is a random number of days not to exceed $maxDays
	 * and no later than $fromDate
	 *
	 * @param array $params = [string $fromDate = yyyy-mm-dd, int $maxDays]
	 * @return DateTime $date
	 */
	public function getDate($entry, $params)
	{
		list($fromDate, $maxDays) = $params;
		$date = new DateTime($fromDate);
		$date->sub(new DateInterval('P' . random_int(0, $maxDays) . 'D'));
		return $date->format('Y-m-d H:i:s');
	}

	public function getPostalCode($entry, $pattern = 1)
	{
		switch ($pattern) {
			// Canadian = ^([A-Za-z]\d[A-Za-z][-]?\d[A-Za-z]\d)$
			case 1 :
				$b = str_replace(['D', 'F', 'I', 'O', 'Q', 'U'], '', $this->alpha);
				$a = str_replace(['W', 'Z'], '', $b);
				$maxB = strlen($b) - 1;
				$maxA = strlen($a) - 1;
				$code = $a[random_int(0, $maxA)]
					  . random_int(0, 9)
					  . $b[random_int(0, $maxB)]
					  . random_int(0, 9)
					  . $b[random_int(0, $maxB)]
					  . random_int(0, 9);
				break;
			// US = ^\d{5}(-\d{4})?$
			case 2 :
				$code = random_int(1, 9) . sprintf('%04d', random_int(0,9999))
					  . '-' . sprintf('%04d', random_int(0,9999));
				break;
			// loosely based on UK system = AA99 9AA
			default :
				return $this->alpha[random_int(0,25)]
					   . $this->alpha[random_int(0,25)]
					   . random_int(1, 99)
					   . ' '
					   . random_int(1, 9)
					   . $this->alpha[random_int(0,25)]
					   . $this->alpha[random_int(0,25)];
		}
	}

}

