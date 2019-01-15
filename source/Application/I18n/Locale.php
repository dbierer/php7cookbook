<?php
namespace Application\I18n;

use IntlCalendar;
use NumberFormatter;
use IntlDateFormatter;
use Locale as PhpLocale;
use InvalidArgumentException;

class Locale extends PhpLocale
{

	const DATE_TYPE_FULL   = IntlDateFormatter::FULL;
	const DATE_TYPE_LONG   = IntlDateFormatter::LONG;
	const DATE_TYPE_MEDIUM = IntlDateFormatter::MEDIUM;
	const DATE_TYPE_SHORT  = IntlDateFormatter::SHORT;

	const ERROR_UNABLE_TO_PARSE = 'ERROR: Unable to parse';
	const ERROR_UNABLE_TO_FORMAT = 'ERROR: Unable to format date';
	const ERROR_ARGS_STRING_ARRAY = 'ERROR: Date must be string YYYY-mm-dd HH:ii:ss or array(y,m,d,h,i,s)';
	const ERROR_CREATE_INTL_DATE_FMT = 'ERROR: Unable to create international date formatter';

	const FALLBACK_LOCALE = 'en';	
	const FALLBACK_CURRENCY = 'GBP';

	protected $localeCode;
	protected $numberFormatter;
	protected $currencyFormatter;
	protected $currencyLookup;
	protected $currencyCode;
	protected $dateFormatter;
	
	/**
	 * Creates instance
	 * If $currencyCodeFile != NULL $this->currencyTable will be built
	 * Currency Table Array or CSV Files should be in this format:
	 * 'countryCode' => ['countryName' => YYYYY, 'currencyName' => Xyyy, 'currencyCode' => XXX, 'currencyNumber' => nnn]
	 * 
	 * @param string $localeString == something like en_GB or fr_FR or en_GB;q=0.7, en;q=0.5
	 * @param Application\I18n\IsoCodesInterface $currencyLookup
	 * 		defines a method getCurrencyCodeFromIso2CountryCode()
	 */
	public function __construct($localeString = NULL, 
								IsoCodesInterface $currencyLookup = NULL)
	{
		if ($localeString) {
			$this->setLocaleCode($localeString);
		} else {
			$this->setLocaleCode($this->getAcceptLanguage());
		}
		
		// lookup currency code
		$this->currencyLookup = $currencyLookup;
		if ($this->currencyLookup) {
			$this->currencyCode = 
				$this->currencyLookup
						->getCurrencyCodeFromIso2CountryCode($this->getCountryCode())
						->currency_code;
		} else {
			$this->currencyCode = self::FALLBACK_CURRENCY;
		}
		
	}	

	public function getAcceptLanguage()
	{
		return $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? self::FALLBACK_LOCALE;
	}
	/**
	 * Sets the array of locale info
	 * 
	 * @param string $acceptHeader == (example from RFC 2616) "da, en-gb;q=0.8, en;q=0.7"
	 */
	public function setLocaleCode($acceptLangHeader)
	{
		$this->localeCode = $this->acceptFromHttp($acceptLangHeader);
	}
	public function getCountryCode()
	{
		return $this->getRegion($this->getLocaleCode());
	}
	public function getCurrencyCode()
	{
		return $this->currencyCode;
	}
	public function getLocaleCode()
	{
		return $this->localeCode;
	}
	// number formatting
	public function getNumberFormatter()
	{
		if (!$this->numberFormatter) {
			$this->numberFormatter = 
				new NumberFormatter($this->getLocaleCode(), NumberFormatter::DECIMAL);
		}
		return $this->numberFormatter;
	}
	public function formatNumber($number)
	{
		return $this->getNumberFormatter()->format($number);
	}		
	public function parseNumber($string)
	{
		$result = $this->getNumberFormatter()->parse($string);
		return ($result) ? $result : self::ERROR_UNABLE_TO_PARSE;
	}
	// currency formatting
	public function getCurrencyFormatter()
	{
		if (!$this->currencyFormatter) {
			$this->currencyFormatter = 
				new NumberFormatter($this->getLocaleCode(), NumberFormatter::CURRENCY);
		}
		return $this->currencyFormatter;	
	}
	public function formatCurrency($currency)
	{
		return $this->getCurrencyFormatter()->formatCurrency($currency, $this->currencyCode);
	}		
	public function parseCurrency($string)
	{
		$result = $this->getCurrencyFormatter()->parseCurrency($string, $this->currencyCode);
		return ($result) ? $result : self::ERROR_UNABLE_TO_PARSE;
	}
	// date formatting
	/**
	 * Returns intlDateFormatter instance
	 * 
	 * @param int self::DATE_TYPE_SHORT | DATE_TYPE_MEDIUM | DATE_TYPE_LONG | DATE_TYPE_FULL
	 * @param IntlDateFormatter $formatter | FALSE
	 * @throws InvalidArgumentException
	 */
	public function getDateFormatter($type)
	{
		switch ($type) {
			case self::DATE_TYPE_SHORT :
				$formatter = new IntlDateFormatter($this->getLocaleCode(), 
													IntlDateFormatter::SHORT, 
													IntlDateFormatter::SHORT);
				break;
			case self::DATE_TYPE_MEDIUM : 
				$formatter = new IntlDateFormatter($this->getLocaleCode(), 
													IntlDateFormatter::MEDIUM, 
													IntlDateFormatter::MEDIUM);
				break;
			case self::DATE_TYPE_LONG :
				$formatter = new IntlDateFormatter($this->getLocaleCode(), 
													IntlDateFormatter::LONG, 
													IntlDateFormatter::LONG);
				break;
			case self::DATE_TYPE_FULL : 
				$formatter = new IntlDateFormatter($this->getLocaleCode(), 
													IntlDateFormatter::FULL, 
													IntlDateFormatter::FULL);
				break;
			default :
				throw new InvalidArgumentException(self::ERROR_CREATE_INTL_DATE_FMT);
		}
		$this->dateFormatter = $formatter;
		return $this->dateFormatter;
	}
	/**
	 * Produces a date formatted according to locale
	 * 
	 * @param string | array $date must be in this form: 
	 * 		(string) YYYY-mm[-dd HH:ii:ss] (where HH = 24 hour value, ii = minutes) or 
	 * 		(array)  YYYY, mm, dd, HH, ii, ss (where HH = 24 hour value, ii = minutes)
	 *      dd, HH, ii, ss are all optional
	 * @param string $type = self::DATE_TYPE_FULL | DATE_TYPE_LONG | DATE_TYPE_MEDIUM | DATE_TYPE_SHORT | (string) custom-format
	 * @param string $timeZone
	 * @return string $formattedDate
	 */
	public function formatDate($date, $type, $timeZone = NULL)
	{
		$result   = NULL;
		$year     = date('Y');
		$month    = date('m');
		$day      = date('d');
		$hour     = 0;
		$minutes  = 0;
		$seconds  = 0;
		// convert $date into values
		if (is_string($date)) {
			list($dateParts, $timeParts) = explode(' ', $date);
			list($year,$month,$day) = explode('-',$dateParts);
			list($hour,$minutes,$seconds) = explode(':',$timeParts);
		} elseif (is_array($date)) {
			list($year,$month,$day,$hour,$minutes,$seconds) = $date;
		} else {
			throw new InvalidArgumentException(self::ERROR_ARGS_STRING_ARRAY);
		}
		$intlDate = IntlCalendar::createInstance($timeZone, $this->getLocaleCode());
		$intlDate->set($year,$month,$day,$hour,$minutes,$seconds);
		$formatter = $this->getDateFormatter($type);
		if ($timeZone) {
			$formatter->setTimeZone($timeZone);
		}
		$result = $formatter->format($intlDate);
		return $result ?? self::ERROR_UNABLE_TO_FORMAT;
	}		
	/**
	 * Produces a timestamp from locale formatted date
	 * 
	 * @param string $date
	 * @param string $type = self::DATE_TYPE_FULL | DATE_TYPE_LONG | DATE_TYPE_MEDIUM | DATE_TYPE_SHORT | (string) custom-format
	 * @return int $timestamp
	 */
	public function parseDate($string, $type = NULL)
	{
		if ($type) {
			$result = $this->getDateFormatter($type)->parse($string);
		} else {
			$tryThese = [self::DATE_TYPE_FULL,self::DATE_TYPE_LONG,self::DATE_TYPE_MEDIUM,self::DATE_TYPE_SHORT];
			foreach ($tryThese as $type) {
				$result = $this->getDateFormatter($type)->parse($string);
				if ($result) {
					break;
				}
			}
		}		
		return ($result) ? $result : self::ERROR_UNABLE_TO_PARSE;
	}
}
