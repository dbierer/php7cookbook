<?php
namespace Application\I18n\Translate;

use Application\I18n\Locale;
use Application\I18n\Translate\Adapter\TranslateAdapterInterface;

class Translation
{
	const DEFAULT_LOCALE_CODE = 'en_GB';
	protected $defaultLocaleCode;
	protected $adapter = array();
	protected $textFilePattern = array();
	public function __construct(TranslateAdapterInterface $adapter, 
								$defaultLocaleCode = NULL, 
								$textFilePattern = NULL)
	{
		if (!$defaultLocaleCode) {
			$this->defaultLocaleCode = self::DEFAULT_LOCALE_CODE;
		} else {
			$this->defaultLocaleCode = $defaultLocaleCode;
		}
		$this->adapter[$this->defaultLocaleCode] = $adapter;
		$this->textFilePattern[$this->defaultLocaleCode] = $textFilePattern;
	}
	public function setAdapter($localeCode, TranslateAdapterInterface $adapter)
	{
		$this->adapter[$localeCode] = $adapter;
	}
	public function setDefaultLocaleCode($localeCode)
	{
		$this->defaultLocaleCode = $localeCode;
	}
	public function setTextFilePattern($localeCode, $pattern)
	{
		$this->textFilePattern[$localeCode] = $pattern;
	}
	public function __invoke($msgid, $locale = NULL)
	{
		if ($locale === NULL) $locale = $this->defaultLocaleCode;
		return $this->adapter[$locale]->translate($msgid);
	}
	public function text($key, $localeCode = NULL)
	{
		if ($localeCode === NULL) $localeCode = $this->defaultLocaleCode;
		$contents = $key;
		if (isset($this->textFilePattern[$localeCode])) {
			$fn = sprintf($this->textFilePattern[$localeCode], $localeCode, $key);
			if (file_exists($fn)) {
				$contents = file_get_contents($fn);
			}
		}
		return $contents;
	}
}
