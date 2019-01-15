<?php
namespace Application\I18n;

class SingleEvent
{	
	public $id;
	public $title;
	public $description;
	public function __construct($id, $title, $description)
	{
		$this->id = $id;
		$this->title = $title;
		$this->description = $description;
	}
}
