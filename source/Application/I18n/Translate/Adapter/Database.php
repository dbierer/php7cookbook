<?php
namespace Application\I18n\Translate\Adapter;

use Exception;
use Application\Database\Connection;
use Application\I18n\Locale;

class Database implements TranslateAdapterInterface
{
	use TranslateAdapterTrait;
	protected $connection;
	protected $statement;
	protected $defaultLocaleCode;
	public function __construct(Locale $locale, Connection $connection, $tableName)
	{
		$this->defaultLocaleCode = $locale->getLocaleCode();
		$this->connection = $connection;
		$sql = 'SELECT msgstr FROM ' . $tableName . ' WHERE locale_code = ? AND msgid = ?';
		$this->statement = $this->connection->pdo->prepare($sql);
	}
	public function translate($msgid, $localeCode = NULL)
	{
		if (!$localeCode) $localeCode = $this->defaultLocaleCode;
		$this->statement->execute([$localeCode, $msgid]);
		return $this->statement->fetchColumn();
	}
}
