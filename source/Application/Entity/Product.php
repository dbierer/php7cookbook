<?php
namespace Application\Entity;

class Product extends Base
{

	const TABLE_NAME = 'products';
	protected $sku = '';
	protected $title = '';
	protected $description = '';
	protected $price = 0.0;
	protected $special = 0;
	protected $link = '';

	protected $mapping = [
		'id'          => 'id',
		'sku'         => 'sku',
		'title'       => 'title',
		'description' => 'description',
		'price'       => 'price',
		'special'     => 'special',
		'link'        => 'link',
	];

	public function getSku() : string
	{
		return $this->sku;
	}
	public function setSku($sku)
	{
		$this->sku = $sku;
	}
	public function getTitle() : string
	{
		return $this->title;
	}
	public function setTitle($title)
	{
		$this->title = $title;
	}
	public function getDescription() : int
	{
		return $this->description;
	}
	public function setDescription($description)
	{
		$this->description = (int) $description;
	}
	public function getPrice() : float
	{
		return $this->price;
	}
	public function setPrice($price)
	{
		$this->price = (float) $price;
	}
	public function getSpecial() : int
	{
		return $this->special;
	}
	public function setSpecial($special)
	{
		$this->special = (int) $special;
	}
	public function getLink() : string
	{
		return $this->link;
	}
	public function setLink($link)
	{
		$this->link = $link;
	}
}
