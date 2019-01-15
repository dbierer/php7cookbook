<?php
namespace Application\Web;

use Exception;
use SplFileObject;

class Access
{

    const ERROR_UNABLE = 'Unable to open file';
    protected $log;
    public    $frequency = array();
    
    public function __construct($filename)
    {
        if (!file_exists($filename)) {
            $message = __METHOD__ . ' : ' . self::ERROR_UNABLE . PHP_EOL;
            $message .= strip_tags($filename) . PHP_EOL;
            throw new Exception($message);
        }
        $this->log = new SplFileObject($filename, 'r');
    }

    public function fileIteratorByLine()
    {
        $count = 0;
        while (!$this->log->eof()) {
            yield $this->log->fgets();
            $count++;
        }
        return $count;
    }
    
    public function getIp($line)
    {
        preg_match('/(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})/', $line, $match);
        return $match[1] ?? '';
    }
}
