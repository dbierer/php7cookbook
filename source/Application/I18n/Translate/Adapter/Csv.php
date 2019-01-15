<?php
namespace Application\I18n\Translate\Adapter;

use Exception;
use SplFileObject;
use Application\I18n\Locale;

class Csv implements TranslateAdapterInterface
{
	use TranslateAdapterTrait;
	const ERROR_NOT_FOUND = 'Translation file not found';
	public function __construct(Locale $locale, $filePattern)
	{
		$translateFileName = sprintf($filePattern, $locale->getLocaleCode());
		if (!file_exists($translateFileName)) {
			error_log(self::ERROR_NOT_FOUND . ':' . $translateFileName);
			throw new Exception(self::ERROR_NOT_FOUND);
		} else {
			$fileObj = new SplFileObject($translateFileName, 'r');
			while ($row = $fileObj->fgetcsv()) {
				$this->translation[$row[0]] = $row[1];
			}
		}
	}
}
