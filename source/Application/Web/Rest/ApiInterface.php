<?php
namespace Application\Web\Rest;

use Application\Web\ { Request, Response };
/**
 * REST API Interface
 * 
 * get($id) == retrieve specific database entry based in $id
 * getList() == get all database entries
 * put($data) == INSERT data
 * post($id, $data) == UPDATE entry based on $id
 * delete($id) == DELETE entry based on $id
 */
interface ApiInterface
{
	public function get(Request $request, Response $response);
	public function put(Request $request, Response $response);
	public function post(Request $request, Response $response);
	public function delete(Request $request, Response $response);
	public function authenticate(Request $request);
}
