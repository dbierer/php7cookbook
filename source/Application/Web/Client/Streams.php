<?php
namespace Application\Web\Client;

use Application\Web\ { Request, Received };

/**
 * HTTP client: PHP streams based
 */
class Streams
{
	
	const BYTES_TO_READ = 4096;
	
	public static function send(Request $request)
	{
		// init vars
		$data = $request->getDataEncoded();
		$received = new Received();
		// process different methods
		switch ($request->getMethod()) {
			case Request::METHOD_GET :
				if ($data) {
					$request->setUri($request->getUri() . '?' . $data);
				}
				$resource = fopen($request->getUri(), 'r');
				break;
			case Request::METHOD_POST :
				$opts = [
					$request->getTransport() => 
					[
						'method'  => Request::METHOD_POST,
						'header'  => Request::HEADER_CONTENT_TYPE . ': ' . Request::CONTENT_TYPE_FORM_URL_ENCODED,
						'content' => $data
					]
				];
				$resource = fopen($request->getUri(), 'w', stream_context_create($opts));
				break;
		}
		return self::getResults($received, $resource);
	}

	protected static function getResults(Received $received, $resource)
	{
		// process results
		$received->setMetaData(stream_get_meta_data($resource));
		if (!empty($received->getMetaDataByKey('wrapper_data')) 
			&& is_array($received->getMetaDataByKey('wrapper_data'))) {
			foreach($received->getMetaDataByKey('wrapper_data') as $item) {
				if (preg_match('!^HTTP/\d\.\d (\d+?) .*?$!', $item, $matches)) {
					$received->setHeaderByKey('status', $matches[1]);
				} else {
					list($key, $value) = explode(':', $item);
					$received->setHeaderByKey($key, trim($value));
				}
			}
		}
		$payload = '';
		while (!feof($resource)) {
			$payload .= fread($resource, self::BYTES_TO_READ);
		}
		if ($received->getHeaderByKey(Received::HEADER_CONTENT_TYPE)) {
			switch (TRUE) {
				case stripos($received->getHeaderByKey(Received::HEADER_CONTENT_TYPE), Received::CONTENT_TYPE_JSON) !== FALSE:
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
