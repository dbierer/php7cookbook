<?php
namespace Application\Web\Rest;

use Application\Web\ { Request, Response };
/**
 * REST API Interface
 * 
 * get(Request $request) == get all database entries
 * put(Request $request) == INSERT data
 * post(Request $request) == SELECT / UPDATE entry(ies) based on Request data params $id and $action
 * delete(Request $request) == DELETE entry based on Request data param $id
 */
abstract class AbstractApi implements ApiInterface
{
	const TOKEN_BYTE_SIZE  = 16;
	protected $registeredKeys;
	abstract public function get(Request $request, Response $response);
	abstract public function put(Request $request, Response $response);
	abstract public function post(Request $request, Response $response);
	abstract public function delete(Request $request, Response $response);
	abstract public function authenticate(Request $request);
	public function __construct($registeredKeys, $tokenField)
	{
		$this->registeredKeys = $registeredKeys;
	}
	public static function generateToken()
	{
		return bin2hex(random_bytes(self::TOKEN_BYTE_SIZE));		
	}
}
