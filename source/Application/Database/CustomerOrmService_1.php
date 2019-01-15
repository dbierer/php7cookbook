<?php
namespace Application\Database;

use PDO;
use PDOException;
use Application\Entity\Customer;
use Application\Entity\Product;
use Application\Entity\Purchase;

class CustomerOrmService_1 extends CustomerService
{
    
	public function fetchByIdAndEmbedPurchases($id)
	{
		return $this->fetchPurchasesForCustomer($this->fetchById($id));
	}
	
	/**
	 * Embeds an array of Application\Entity\Purchase entities into $cust
	 * Each Purchase entity has an Application\Entity\Product instance embedded
	 * 
	 * @param Application\Entity\Customer $cust
	 * @return Application\Entity\Customer $cust
	 */
	protected function fetchPurchasesForCustomer(Customer $cust)
	{
		// pull purchases + product info
		$sql = 'SELECT u.*,r.*,u.id AS purch_id '
			 . 'FROM purchases AS u '
			 . 'JOIN products AS r '
			 . 'ON r.id = u.product_id '
			 . 'WHERE u.customer_id = :id '
			 . 'ORDER BY u.date';
		$stmt = $this->connection->pdo->prepare($sql);
		$stmt->execute(['id' => $cust->getId()]);
		
		// get purchase + product info
		while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
			
			// define and populate Product entity
			$product = Product::arrayToEntity($result, new Product());
			$product->setId($result['product_id']);
			
			// define and populate Purchase entity
			$purch = Purchase::arrayToEntity($result, new Purchase());
			$purch->setId($result['purch_id']);
			
			// embed Product entity in Purchase entity
			$purch->setProduct($product);
			
			// add Purchase entity to Customer entity "purchases" array
			$cust->addPurchase($purch);
			
		}
		
		return $cust;
		
	}
	
}
