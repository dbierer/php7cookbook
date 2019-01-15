<?php
namespace Application\I18n;

use PDO;
use Application\Database\Connection;

class IsoCodesDb implements IsoCodesInterface
{
	protected $isoTableName;
	protected $iso2FieldName;
	protected $pdo;
	protected $connection;
	public function __construct(Connection $connection, $isoTableName, $iso2FieldName)
	{
		$this->connection = $connection;
		$this->isoTableName = $isoTableName;
		$this->iso2FieldName = $iso2FieldName;
	}
	public function getCurrencyCodeFromIso2CountryCode($iso2) : IsoCodes
	{
		$sql = sprintf('SELECT * FROM %s WHERE %s = ?', 
						$this->isoTableName, 
						$this->iso2FieldName);
		$stmt = $this->connection->pdo->prepare($sql);
		$stmt->execute([$iso2]);
		$data = $stmt->fetch(PDO::FETCH_ASSOC);
		return new IsoCodes($data);
	}
}
	
