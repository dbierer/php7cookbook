<?php
namespace Application\Web;

/**
 * Generic class which encapsulates an HTTP request / response
 * You can use this class to either generate, or receive, requests/reponses over HTTP / HTTPS
 */
class Received extends AbstractHttp
{

	/**
	 * Builds a request object
	 * If incoming params are NULL, values default to $_SERVER
	 * 
	 * @param string $uri
	 * @param string $method
	 * @param array $headers
	 * @param array $data
	 */
	public function __construct($uri = NULL, $method = NULL, array $headers = NULL, array $data = NULL, array $cookies = NULL)
	{
		$this->uri = $uri;
		$this->method = $method;
		$this->headers = $headers;
		$this->data = $data;
		$this->cookies = $cookies;
		$this->setTransport();
	}
	
}
