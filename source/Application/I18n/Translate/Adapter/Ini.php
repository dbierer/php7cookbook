<?php
namespace Application\I18n\Translate\Adapter;

use Exception;
use Application\I18n\Locale;

class Ini implements TranslateAdapterInterface
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
			$this->translation = parse_ini_file($translateFileName);
		}
	}
}
