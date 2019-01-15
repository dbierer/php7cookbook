<?php
namespace Application\I18n;

class IsoCodes
{
	public $name;
	public $iso2;
	public $iso3;
	public $iso_numeric;
	public $iso_3166;
	public $currency_name;
	public $currency_code;
	public $currency_number;
	public function __construct(array $data = NULL)
	{
		$vars = get_object_vars($this);
		foreach ($vars as $key => $value) {
			$this->$key = $data[$key] ?? NULL;
		}
	}
}
