<?php
namespace Application\Web;

/**
 * Generic class which encapsulates an HTTP request
 * You can use this class to either create requests to send to a web service, or encapsulate a request received via PHP
 */
class Response extends AbstractHttp
{
	protected $status;
	/**
	 * Builds a response object
	 * If incoming params are NULL, values default to $_SERVER
	 * 
	 * @param Request $request
	 */
	public function __construct(Request $request = NULL, $status = NULL, $contentType = NULL)
	{
		if ($request) {
			$this->uri = $request->getUri();
			$this->method = $request->getMethod();
			$this->data = $request->getData();
			$this->cookies = $request->getCookies();
			$this->setTransport();
		}
		// process headers
		$this->processHeaders($contentType);
		if ($status) {
			$this->setStatus($status);
		}
	}
	protected function processHeaders($contentType)
	{
		if (!$contentType) {
			$this->setHeaderByKey(self::HEADER_CONTENT_TYPE, self::CONTENT_TYPE_JSON);
		} else {
			$this->setHeaderByKey(self::HEADER_CONTENT_TYPE, $contentType);
		}
	}
	public function setStatus($status)
	{
		$this->status = $status;
	}
	public function getStatus()
	{
		return $this->status;
	}
}
