<?php
namespace Application\Web;

/**
 * Generic class which encapsulates an HTTP request / response
 * You can use this class to either generate, or receive, requests/reponses over HTTP / HTTPS
 */
class AbstractHttp
{
	
	const METHOD_GET = 'GET';
	const METHOD_POST = 'POST';
	const METHOD_PUT = 'PUT';
	const METHOD_DELETE = 'DELETE';
	const CONTENT_TYPE_HTML = 'text/html';
	const CONTENT_TYPE_JSON = 'application/json';
	const CONTENT_TYPE_FORM_URL_ENCODED = 'application/x-www-form-urlencoded';
	const HEADER_CONTENT_TYPE = 'Content-Type';
	const TRANSPORT_HTTP = 'http';
	const TRANSPORT_HTTPS = 'https';
	const STATUS_200 = '200';
	const STATUS_401 = '401';
	const STATUS_500 = '500';
	
	protected $uri;
	protected $method;
	protected $headers;
	protected $cookies;
	protected $metaData;
	protected $transport;
	protected $data = array();

	// special requirements
	public function setUri($uri, array $params = NULL)
	{
		$this->uri = $uri;
		$first = TRUE;
		if ($params) {
			$this->uri .= '?' . http_build_query($params);
		}
	}
	public function getDataEncoded()
	{
		return http_build_query($this->getData());
	}
	public function setTransport($transport = NULL)
	{
		if ($transport) {
			$this->transport = $transport;
		} else {
			if (substr($this->uri, 0, 5) == self::TRANSPORT_HTTPS) {
				$this->transport = self::TRANSPORT_HTTPS;
			} else {
				$this->transport = self::TRANSPORT_HTTP;
			}
		}
	}
	
	// misc setters/getters
	public function getUri()
	{
		return $this->uri ?? NULL;
	}
	public function setMethod($method)
	{
		$this->method = $method;
	}
	public function getMethod()
	{
		return $this->method ?? self::METHOD_GET;
	}
	public function setHeaders(array $headers)
	{
		$this->headers = $headers;
	}
	public function getHeaders()
	{
		return $this->headers ?? NULL;
	}
	public function getHeaderByKey($key)
	{
		return $this->headers[$key] ?? NULL;
	}
	public function setCookies($cookies)
	{
		$this->cookies = $cookies;
	}
	public function getCookies()
	{
		return $this->cookies ?? NULL;
	}
	public function setData($data)
	{
		$this->data = $data;
	}
	public function getData()
	{
		return $this->data ?? NULL;
	}
	public function getTransport()
	{
		return $this->transport ?? NULL;
	}
	public function setMetaData($metaData)
	{
		$this->metaData = $metaData;
	}
	public function getMetaData()
	{
		return $this->metaData ?? NULL;
	}
	
	// get/set by key
	public function setHeaderByKey($key, $value)
	{
		$this->headers[$key] = $value;
	}
	public function getDataByKey($key)
	{
		return $this->data[$key] ?? NULL;
	}
	public function getMetaDataByKey($key)
	{
		return $this->metaData[$key] ?? NULL;
	}
	
}
