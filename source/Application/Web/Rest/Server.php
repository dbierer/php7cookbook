<?php
namespace Application\Web\Rest;

use Application\Web\ { Request, Response, Received };

/**
 * REST server
 */
class Server
{
	protected $api;
	public function __construct(ApiInterface $api)
	{
		$this->api = $api;
	}
	public function listen()
	{
		$request  = new Request();
		$response = new Response($request);
		
		// get incoming data + assign to request object
		$getPost  = $_REQUEST ?? array();
		$jsonData = json_decode(file_get_contents('php://input'),true);
		$jsonData = $jsonData ?? array();
		$request->setData(array_merge($getPost,$jsonData));
		
		// deal with authentication
		if (!$this->api->authenticate($request)) {
			$response->setStatus(Request::STATUS_401);
			echo $this->api::ERROR;
			exit;
		}
		
		// handle request based on method
		$id = $request->getData()[$this->api::ID_FIELD] ?? NULL;
		switch (strtoupper($request->getMethod())) {
			case Request::METHOD_POST :
				$this->api->post($request, $response);
				break;
			case Request::METHOD_PUT :
				$this->api->put($request, $response);
				break;
			case Request::METHOD_DELETE :
				$this->api->delete($request, $response);
				break;
			case Request::METHOD_GET :
			default :
				// return all if no params
				$this->api->get($request, $response);
		}
		$this->processResponse($response);
		echo json_encode($response->getData());
	}
	protected function processResponse($response)
	{
		// set headers
		if ($response->getHeaders()) {
			foreach ($response->getHeaders() as $key => $value) {
				header($key . ': ' . $value, TRUE, $response->getStatus());
			}
		}				
		// set content type to JSON
		header(Request::HEADER_CONTENT_TYPE . ': ' . Request::CONTENT_TYPE_JSON, TRUE);
		// set cookies
		if ($response->getCookies()) {
			foreach ($response->getCookies() as $key => $value) {
				setcookie($key, $value);
			}
		}
	}
}
