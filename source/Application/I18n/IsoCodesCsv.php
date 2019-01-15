<?php
namespace Application\I18n;

use SplFileObject;

class IsoCodesCsv implements IsoCodesInterface
{
	const ERROR_FILE_OPEN = 'ERROR: problem opening file';
	protected $isoCsvFile;
	protected $iso2Column;
	public function __construct($isoCsvFile, $iso2Column)
	{
		$this->iso2Column = $iso2Column;
		try {
			$this->isoCsvFile = new SplFileObject($isoCsvFile, 'r');
		} catch (Exception $e) {
			error_log($e->getMessage());
			throw new Exception(self::ERROR_FILE_OPEN);
		}
	}
	public function getCurrencyCodeFromIso2CountryCode($iso2) : IsoCodes
	{
		$isoCodes = new IsoCodes();
		while ($row = $this->isoCsvFile->fgetcsv()) {
			if ($row[$this->iso2Column] == $iso2) {
				$var = get_object_vars($isoCodes);
				$count = 0;
				foreach ($var as $key => $value) {
					$isoCodes->$key = $row[$count++];
				}
				break;
			}
		}
		return $isoCodes;
	}
}
	
