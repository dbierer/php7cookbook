<?php
namespace Application\Web\Soap;

use PDO;
/**
 * SOAP API
 * 
 */
class ProspectsApi
{
	const TABLE_NAME = 'prospects';
	const ERROR = 'ERROR';
	const ERROR_NOT_FOUND = 'ERROR: Not Found';
	const SUCCESS = 'SUCCESS';
	const SUCCESS_UPDATE = 'SUCCESS: update succeeded';
	const SUCCESS_INSERT = 'SUCCESS: insert succeeded';
	const SUCCESS_DELETE = 'SUCCESS: delete succeeded';
	const ID_FIELD = 'id';			// field name of primary key
	const EMAIL_FIELD = 'email';    // unique column
	const TOKEN_FIELD = 'token';	// field which must be present in data section of all requests
	const LIMIT_FIELD = 'limit';
	const OFFSET_FIELD = 'offset';
	const DEFAULT_LIMIT = 20;
	const DEFAULT_OFFSET = 0;
	const STATUS_200 = '200';
	const STATUS_401 = '401';
	const STATUS_500 = '500';

	protected $registerKeys;
	protected $pdo;
	
	protected $mapping = [
		'id' => 'id',
		'first_name' => 'first_name',
		'last_name' => 'last_name',
		'address' => 'address',
		'city' => 'city',
		'state_province' => 'state_province',
		'postal_code' => 'postal_code',
		'phone' => 'phone',
		'country' => 'country',
		'email' => 'email',
		'status' => 'status',
		'budget' => 'budget',
		'last_updated' => 'last_updated',	
	];

	public function __construct($pdo, $registeredKeys)
	{
		$this->pdo = $pdo;
		$this->registeredKeys = $registeredKeys;
	}
	/**
	 * @param array $request
	 * @param array $response
	 * @return array $response
	 */
	public function get(array $request, array $response)
	{
		if (!$this->authenticate($request)) return FALSE;
		$result = array();
		$id = $request[self::ID_FIELD] ?? 0;
		$email = $request[self::EMAIL_FIELD] ?? 0;
		if ($id > 0) {
			$result = $this->fetchById($id);	
			$response[self::ID_FIELD] = $id;
		} elseif ($email) {
			$result = $this->fetchByEmail($email);
			$response[self::ID_FIELD] = $result[self::ID_FIELD] ?? 0;
		} else {
			$limit  = $request[self::LIMIT_FIELD] ?? self::DEFAULT_LIMIT;
			$offset = $request[self::OFFSET_FIELD] ?? self::DEFAULT_OFFSET;
			$result = [];
			foreach ($this->fetchAll($limit, $offset) as $row) {
				$result[] = $row;
			}
		}
		$response = $this->processResponse($result, $response, self::SUCCESS, self::ERROR);
		return $response;
	}
	/**
	 * @param array $request
	 * @param array $response
	 * @return array $response
	 */
	public function put(array $request, array $response)
	{
		if (!$this->authenticate($request)) return FALSE;
		if (isset($result[self::ID_FIELD])) unset($result[self::ID_FIELD]);
		$email = $request['data'][self::EMAIL_FIELD] ?? '';
		$ok = $this->insert($request['data']);
		if ($ok) {
			$result = $this->fetchByEmail($email);
			if ($result) {
				$response[self::ID_FIELD] = $result['data'][self::ID_FIELD];
			}
		}
		$response[self::EMAIL_FIELD] = $email;
		return $this->processResponse($result, $response, self::SUCCESS_INSERT, self::ERROR);
	}
	/**
	 * @param array $request
	 * @param array $response
	 * @return array $response
	 */
	public function post(array $request, array $response)
	{
		if (!$this->authenticate($request)) return FALSE;
		$id = $request[self::ID_FIELD] ?? 0;
		if (!$id) {
			return $this->processResponse(FALSE, $response, self::SUCCESS_UPDATE, self::ERROR);
		}
		$reqData = $request['data'];
		$custData = $this->fetchById($id);
		$updateData = array_merge($custData, $reqData);
		$result = $this->update($updateData);
		$response[self::ID_FIELD] = $id;
		return $this->processResponse($result, $response, self::SUCCESS_UPDATE, self::ERROR);
	}
	/**
	 * @param array $request
	 * @param array $response
	 * @return array $response
	 */
	public function delete(array $request, array $response)
	{
		if (!$this->authenticate($request)) return FALSE;
		$id = $request[self::ID_FIELD] ?? 0;
		$cust = $this->fetchById($id);
		$result = $this->remove($id);
		$response[self::ID_FIELD] = $id;
		return $this->processResponse($result, $response, self::SUCCESS_DELETE, self::ERROR);
	}
	
	protected function processResponse($result, $response, $success_code, $error_code)
	{
		if ($result) {
			$response['data'] = $result;
			$response['code'] = $success_code;
			$response['status'] = self::STATUS_200;
		} else {
			$response['data'] = FALSE;
			$response['code'] = self::ERROR_NOT_FOUND;
			$response['status'] = self::STATUS_500;
		}
		return $response;
	}
	
	protected function fetchById($id)
	{
		$sql = 'SELECT * FROM ' . self::TABLE_NAME . ' WHERE ' . self::ID_FIELD . ' = :id';
		$params = [self::ID_FIELD => $id];
		return $this->processSql($sql, $params);
	}
	
	protected function fetchByEmail($email)
	{
		$sql = 'SELECT * FROM ' . self::TABLE_NAME . ' WHERE email = :email';
		$params = ['email' => $email];
		return $this->processSql($sql, $params);
	}
	
	protected function fetchAll($limit, $offset)
	{
		$sql = 'SELECT * FROM ' . self::TABLE_NAME . ' LIMIT ' . (int) $limit . ' OFFSET ' . (int) $offset;
		$stmt = $this->pdo->prepare($sql);
		$result = $stmt->execute($params);
		while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) yield $row;
	}
	
	protected function remove($id)
	{
		$sql = 'DELETE FROM ' . self::TABLE_NAME . ' WHERE ' . self::ID_FIELD . ' = :id';
		$params = ['id' => $id];
		return $this->processSql($sql, $params);
	}
	
	protected function insert($data)
	{
		$sql = 'INSERT INTO ' . self::TABLE_NAME . ' SET';
		$custData = array();
		$params = $this->addToSet($data, $sql);
		return $this->processSql($sql, $params);
	}

	protected function update($data)
	{
		$sql = 'UPDATE ' . self::TABLE_NAME . ' SET';
		$custData = array();
		$id = $data['id'] ?? 0;
		// if ID and custData, do update
		if ($id) {
			$custData = $this->fetchById($id);
			if ($custData) {
				$data = array_merge($custData, $data);
			}
			$params = $this->addToSet($data, $sql);
			$sql .= ' WHERE ' . self::ID_FIELD . ' = :id';
			$params[self::ID_FIELD] = $id;
			return $this->processSql($sql, $params);
		} else {
			return FALSE;
		}
	}
	
	protected function addToSet($data, &$sql)
	{
		// go through data set
		$params = array();
		foreach ($data as $key => $value) {
			if (isset($this->mapping[$key]) && $key != self::ID_FIELD) {
				$sql .= ' ' . $key . '= :' . $key . ',';
				$params[$key] = $value;
			}
		}
		$sql = substr($sql, 0, -1);
		return $params;
	}
	
	protected function processSql($sql, $params = [])
	{
		try {
			$stmt = $this->pdo->prepare($sql);
			$result = $stmt->execute($params);
			if (strpos($sql, 'SELECT') === 0) {
				return $stmt->fetch(PDO::FETCH_ASSOC);
			} else {
				return $result;
			}
		} catch (PDOException $e) {
			error_log($e->getMessage());
			return FALSE;
		}
	}
	
	/**
	 * @param array $request
	 * @return boolean $result
	 */
	protected function authenticate($request)
	{
		$result = FALSE;
		$authToken = $request[self::TOKEN_FIELD] ?? FALSE;
		if (in_array($authToken, $this->registeredKeys, TRUE)) {
			$result = TRUE;
		} else {
			$result = FALSE;
		}
		return $result;
	}
	
}
