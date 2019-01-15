<?php
namespace Application\Database;

use PDO;
use PDOException;
use Application\Entity\Customer;
use Application\Entity\Product;
use Application\Entity\Purchase;

class CustomerOrmService_2 extends CustomerService
{

	protected $products = array();
	protected $purchPreparedStmt = NULL;
	protected $prodPreparedStmt = NULL;
	
	/**
	 * Embeds an array of Application\Entity\Purchase entities into $cust
	 * Each Purchase entity has an Application\Entity\Product instance embedded
	 * 
	 * @param Application\Entity\Customer $cust
	 * @return Application\Entity\Customer $cust
	 */
	public function fetchPurchasesForCustomer(Customer $cust)
	{
		// pull array of purchase IDs
		$sql = 'SELECT id '
			 . 'FROM purchases AS u '
			 . 'WHERE u.customer_id = :id '
			 . 'ORDER BY u.date';
		$stmt = $this->connection->pdo->prepare($sql);
		$stmt->execute(['id' => $cust->getId()]);
		
		// embed anonymous functions which perform product purchase lookups
		while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
			
			// add anonymous function purchases lookup to Customer entity "purchases" array
			$cust->addPurchaseLookup(
				$result['id'],
				function ($purchId, $service) { 
					$purchase = $service->fetchPurchaseById($purchId);
					$product  = $service->fetchProductById($purchase->getProductId());
					$purchase->setProduct($product);
					return $purchase;
				}
			);
			
		}
		return $cust;		
	}
		
	public function fetchPurchaseById($purchId)
	{
		// pull purchases using single prepared statement
		if (!$this->purchPreparedStmt) {
			$sql = 'SELECT * FROM purchases WHERE id = :id';
			$this->purchPreparedStmt = $this->connection->pdo->prepare($sql);
		}
		$this->purchPreparedStmt->execute(['id' => $purchId]);
		$result = $this->purchPreparedStmt->fetch(PDO::FETCH_ASSOC);
		return Purchase::arrayToEntity($result, new Purchase());
	}
		
	public function fetchProductById($prodId)
	{
		// does product info already exist?
		if (!isset($this->products[$prodId])) {
			// pull purchases using single prepared statement
			if (!$this->prodPreparedStmt) {
				$sql = 'SELECT * FROM products WHERE id = :id';
				$this->prodPreparedStmt = $this->connection->pdo->prepare($sql);
			}
			$this->prodPreparedStmt->execute(['id' => $prodId]);
			$result = $this->prodPreparedStmt->fetch(PDO::FETCH_ASSOC);
			$this->products[$prodId] = Product::arrayToEntity($result, new Product());
		}
		return $this->products[$prodId];
	}
	
}
