<?php
namespace Application\I18n;

interface IsoCodesInterface
{
	public function getCurrencyCodeFromIso2CountryCode($iso2) : IsoCodes;
}
	
