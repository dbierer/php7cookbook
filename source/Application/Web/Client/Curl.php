<?php
namespace Application\Web\Client;

use Application\Web\ { Request, Received };

/**
 * HTTP client: PHP streams based
 */
class Curl
{
	
	public static function send(Request $request)
	{
		// init vars
		$data = $request->getDataEncoded();
		$received = new Received();
		// process different methods
		switch ($request->getMethod()) {
			case Request::METHOD_GET :
				$uri = ($data) ? $request->getUri() . '?' . $data : $request->getUri();
				$options = [
					CURLOPT_URL => $uri,
					CURLOPT_HEADER => 0,
					CURLOPT_RETURNTRANSFER => TRUE,
					CURLOPT_TIMEOUT => 4
				];
				break;
			case Request::METHOD_POST :
				$options = [
					CURLOPT_POST => 1,
					CURLOPT_HEADER => 0,
					CURLOPT_URL => $request->getUri(),
					CURLOPT_FRESH_CONNECT => 1,
					CURLOPT_RETURNTRANSFER => 1,
					CURLOPT_FORBID_REUSE => 1,
					CURLOPT_TIMEOUT => 4,
					CURLOPT_POSTFIELDS => $data
				];
				break;
		}
		$ch = curl_init();
		curl_setopt_array($ch, ($options));
		if( ! $result = curl_exec($ch))
		{
			trigger_error(curl_error($ch));
		}
		$received->setMetaData(curl_getinfo($ch));
		curl_close($ch);
		return self::getResults($received, $result);
	}

	protected static function getResults(Received $received, $payload)
	{
		if ($received->getMetaDataByKey('content_type')) {
			switch (TRUE) {
				case stripos($received->getMetaDataByKey('content_type'), Received::CONTENT_TYPE_JSON) !== FALSE:
					$received->setData(json_decode($payload));
					break;
				default :
					$received->setData($payload);
					break;
			}
		}
		return $received;
	}
	
}
