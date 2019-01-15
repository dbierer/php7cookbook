<?php
namespace Application\Entity;

class Purchase extends Base
{

	const TABLE_NAME = 'purchases';
	protected $transaction = '';
	protected $date = NULL;
	protected $quantity = 0;
	protected $salePrice = 0.0;
	protected $customerId = 0;
	protected $productId = 0;
	// added to implement object relational mapping
	protected $product = NULL;
	
	protected $mapping = [
		'id'            => 'id',
		'transaction'   => 'transaction',
		'date'			=> 'date',
		'quantity'      => 'quantity',
		'sale_price'    => 'salePrice',
		'customer_id'   => 'customerId',
		'product_id'    => 'productId',
	];

	public function getTransaction() : string
	{
		return $this->transaction;
	}
	public function setTransaction($transaction)
	{
		$this->transaction = $transaction;
	}
	public function setDate($date)
	{
		$this->date = $date;
	}
	public function getDate() : string
	{
		return $this->date;
	}
	public function getQuantity() : int
	{
		return $this->quantity;
	}
	public function setQuantity($quantity)
	{
		$this->quantity = (int) $quantity;
	}
	public function getSalePrice() : float
	{
		return $this->salePrice;
	}
	public function setSalePrice($salePrice)
	{
		$this->salePrice = (float) $salePrice;
	}
	public function getCustomerId() : int
	{
		return $this->customerId;
	}
	public function setCustomerId($customerId)
	{
		$this->customerId = (int) $customerId;
	}
	public function getProductId() : int
	{
		return $this->productId;
	}
	public function setProductId($productId)
	{
		$this->productId = (int) $productId;
	}
	// added to implement object relational mapping
	public function getProduct()
	{
		return $this->product;
	}
	// added to implement object relational mapping
	public function setProduct(Product $product)
	{
		$this->product = $product;
	}
}
