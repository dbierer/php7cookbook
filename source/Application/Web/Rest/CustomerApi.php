<?php
namespace Application\Web\Rest;

use Application\Web\ { Request, Response, Received };
use Application\Entity\Customer;
use Application\Database\ { Connection, CustomerService };

/**
 * REST API
 * 
 * All output is JSON
 * All data is expected to be in JSON format
 */
class CustomerApi extends AbstractApi
{
	const ERROR = 'ERROR';
	const ERROR_NOT_FOUND = 'ERROR: Not Found';
	const SUCCESS_UPDATE = 'SUCCESS: update succeeded';
	const SUCCESS_DELETE = 'SUCCESS: delete succeeded';
	const ID_FIELD = 'id';			// field name of primary key
	const TOKEN_FIELD = 'token';	// field which must be present in data section of all requests
	const LIMIT_FIELD = 'limit';
	const OFFSET_FIELD = 'offset';
	const DEFAULT_LIMIT = 20;
	const DEFAULT_OFFSET = 0;

	protected $service;

	public function __construct($registeredKeys, $dbparams, $tokenField = NULL)
	{
		parent::__construct($registeredKeys, $tokenField);
		$this->service = new CustomerService(new Connection($dbparams));
	}
	public function get(Request $request, Response $response)
	{
		$result = array();
		$id = $request->getDataByKey(self::ID_FIELD) ?? 0;
		if ($id > 0) {
			$result = $this->service->fetchById($id)->entityToArray();	
		} else {
			$limit  = $request->getDataByKey(self::LIMIT_FIELD) ?? self::DEFAULT_LIMIT;
			$offset = $request->getDataByKey(self::OFFSET_FIELD) ?? self::DEFAULT_OFFSET;
			$result = [];
			foreach ($this->service->fetchAll($limit, $offset) as $row) {
				$result[] = $row;
			}
		}
		if ($result) {
			$response->setData($result);
			$response->setStatus(Request::STATUS_200);
		} else {
			$response->setData([self::ERROR_NOT_FOUND]);
			$response->setStatus(Request::STATUS_500);
		}
	}
	public function put(Request $request, Response $response)
	{
		$cust = Customer::arrayToEntity($request->getData(), new Customer());
		if ($newCust = $this->service->save($cust)) {
			$response->setData(['success' => self::SUCCESS_UPDATE, 'id' => $newCust->getId()]);
			$response->setStatus(Request::STATUS_200);
		} else {
			$response->setData([self::ERROR]);
			$response->setStatus(Request::STATUS_500);
		}			
	}
	public function post(Request $request, Response $response)
	{
		$id = $request->getDataByKey(self::ID_FIELD) ?? 0;
		$reqData = $request->getData();
		$custData = $this->service->fetchById($id)->entityToArray();
		$updateData = array_merge($custData, $reqData);
		$updateCust = Customer::arrayToEntity($updateData, new Customer());
		if ($this->service->save($updateCust)) {
			$response->setData(['success' => self::SUCCESS_UPDATE, 'id' => $updateCust->getId()]);
			$response->setStatus(Request::STATUS_200);
		} else {
			$response->setData([self::ERROR]);
			$response->setStatus(Request::STATUS_500);
		}			
	}
	public function delete(Request $request, Response $response)
	{
		$id = $request->getDataByKey(self::ID_FIELD) ?? 0;
		$cust = $this->service->fetchById($id);
		if ($cust && $this->service->remove($cust)) {
			$response->setData(['success' => self::SUCCESS_DELETE, 'id' => $id]);
			$response->setStatus(Request::STATUS_200);
		} else {
			$response->setData([self::ERROR_NOT_FOUND]);
			$response->setStatus(Request::STATUS_500);
		}			
	}
	public function authenticate(Request $request)
	{
		$authToken = $request->getDataByKey(self::TOKEN_FIELD) ?? FALSE;
		if (in_array($authToken, $this->registeredKeys, TRUE)) {
			return TRUE;
		} else {
			return FALSE;
		}
	}
}
