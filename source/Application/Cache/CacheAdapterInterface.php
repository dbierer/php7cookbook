<?php
/**
 * Requirements for a cache adapter
 */
namespace Application\Cache;

interface  CacheAdapterInterface
{
    public function hasKey($key);
	public function getFromCache($key, $group);
	public function saveToCache($key, $data, $group);
	public function removeByKey($key);
	public function removeByGroup($group);
}
