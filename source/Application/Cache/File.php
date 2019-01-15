<?php
/**
 * Requirements for a cache adapter
 */
namespace Application\Cache;

use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

class File implements CacheAdapterInterface
{
    protected $dir;
    protected $prefix;
    protected $suffix;

    public function __construct($dir, $prefix = NULL, $suffix = NULL)
    {
        if (!file_exists($dir)) {
			error_log(__METHOD__ . ':' . Constants::ERROR_DIR_NOT);
			throw new Exception(Constants::ERROR_DIR_NOT);
        }
        $this->dir = $dir;
        $this->prefix = $prefix ?? Constants::DEFAULT_PREFIX;
        $this->suffix = $suffix ?? Constants::DEFAULT_SUFFIX;
    }

    public function hasKey($key)
    {
        $action = function ($name, $md5Key, &$item) {
            if (strpos($name, $md5Key) !== FALSE) {
                $item ++;
            }
        };

        return $this->findKey($key, $action);
    }

	public function getFromCache($key, $group = Constants::DEFAULT_GROUP)
    {
        $fn = $this->dir . '/' . $group . '/' . $this->prefix . md5($key) . $this->suffix;
        if (file_exists($fn)) {
            foreach (file($fn) as $line) {
                yield $line;
            }
        } else {
            return array();
        }
    }

	public function saveToCache($key, $data, $group = Constants::DEFAULT_GROUP)
    {
        $baseDir = $this->dir . '/' . $group;
        if (!file_exists($baseDir)) {
            mkdir($baseDir);
        }
        $fn = $baseDir . '/' . $this->prefix . md5($key) . $this->suffix;
        return file_put_contents($fn, json_encode($data));
    }

    protected function findKey($key, callable $action)
    {
        $md5Key = md5($key);
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->dir),
            RecursiveIteratorIterator::SELF_FIRST);
        $item = 0;
        foreach ($iterator as $name => $obj) {
            $action($name, $md5Key, $item);
        }
        return $item;
    }

	public function removeByKey($key)
    {
        $action = function ($name, $md5Key, &$item) {
            if (strpos($name, $md5Key) !== FALSE) {
                unlink($name);
                $item++;
            }
        };
        return $this->findKey($key, $action);
    }

	public function removeByGroup($group)
    {
        $removed = 0;
        $baseDir = $this->dir . '/' . $group;
        $pattern = $baseDir . '/' . $this->prefix . '*' . $this->suffix;
        foreach (glob($pattern) as $file) {
            unlink($file);
            $removed++;
        }
        return $removed;
    }
}
