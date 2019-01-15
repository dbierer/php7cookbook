<?php
/**
 * Implements cache
 *
 * Methods:
 * hasKey()
 * getFromCache()
 * saveToCache()
 * removeByKey()
 * removeByGroup()
 *
 */
namespace Application\Cache;

use Psr\Http\Message\RequestInterface;
use Application\MiddleWare\ { Request, Response, TextStream };

class Core
{

	/**
	 * Initialize Cache vars
	 *
	 * @param CacheAdapterInterface $adapter = cache adapter
	 */
	public function __construct(CacheAdapterInterface $adapter)
	{
		$this->adapter = $adapter;
	}

	public function hasKey(RequestInterface $request)
	{
        $key = $request->getUri()->getQueryParams()['key'] ?? '';
		$result = $this->adapter->hasKey($key);
	}

	public function getFromCache(RequestInterface $request)
	{
        $text = array();
        $key = $request->getUri()->getQueryParams()['key'] ?? '';
        $group = $request->getUri()->getQueryParams()['group'] ?? Constants::DEFAULT_GROUP;
        $results = $this->adapter->getFromCache($key, $group);
        if (!$results) {
            $code = 204;
        } else {
            $code = 200;
            foreach ($results as $line) {
                $text[] = $line;
            }
        }
        if (!$text || count($text) == 0) {
            $code = 204;
        }
        $body = new TextStream(json_encode($text));
        return (new Response())
                ->withStatus($code)
                ->withBody($body);
	}

	public function saveToCache(RequestInterface $request)
	{
        $text = array();
        $key = $request->getUri()->getQueryParams()['key'] ?? '';
        $group = $request->getUri()->getQueryParams()['group'] ?? Constants::DEFAULT_GROUP;
        $data = $request->getBody()->getContents();
        $results = $this->adapter->saveToCache($key, $data, $group);
        if (!$results) {
            $code = 204;
        } else {
            $code = 200;
            $text[] = $results;
        }
        $body = new TextStream(json_encode($text));
        return (new Response())
                ->withStatus($code)
                ->withBody($body);
	}

	public function removeByKey(RequestInterface $request)
	{
        $text = array();
        $key = $request->getUri()->getQueryParams()['key'] ?? '';
        $results = $this->adapter->removeByKey($key);
        if (!$results) {
            $code = 204;
        } else {
            $code = 200;
            $text[] = $results;
        }
        $body = new TextStream(json_encode($text));
        return (new Response())
                ->withStatus($code)
                ->withBody($body);
	}

	public function removeByGroup(RequestInterface $request)
	{
        $text = array();
        $group = $request->getUri()->getQueryParams()['group'] ?? Constants::DEFAULT_GROUP;
        $results = $this->adapter->removeByGroup($group);
        if (!$results) {
            $code = 204;
        } else {
            $code = 200;
            $text[] = $results;
        }
        $body = new TextStream(json_encode($text));
        return (new Response())
                ->withStatus($code)
                ->withBody($body);
	}

}
