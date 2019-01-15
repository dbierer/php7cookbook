<?php
namespace Application\I18n\Translate\Adapter;

trait TranslateAdapterTrait
{
	protected $translation;
	public function translate($msgid)
	{
		return $this->translation[$msgid] ?? $msgid;
	}
}
