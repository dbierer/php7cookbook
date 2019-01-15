<?php
namespace Application\Iterator;

use Exception;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RecursiveRegexIterator;
use RegexIterator;

class Directory
{

    const ERROR_UNABLE = 'ERROR: Unable to read directory';
    const DEFAULT_PATTERN = '/^.+\.php$/i';

    protected $path;
    protected $rdi;     // recursive directory iterator

    public function __construct($path)
    {
        try {
            $this->rdi = new RecursiveIteratorIterator(
                            new RecursiveDirectoryIterator($path),
                            RecursiveIteratorIterator::SELF_FIRST);
        } catch (\Throwable $e) {
            $message = __METHOD__ . ' : ' . self::ERROR_UNABLE . PHP_EOL;
            $message .= strip_tags($path) . PHP_EOL;
            echo $message;
            exit;
        }
    }

    /**
     * Mimics the output of the "ls -l -R" command
     * -rw-rw-r-- 1 aed aed  4667 Jan 14 17:10 chap_02_config.php
     *
     * @param string $pattern
     * @return Generator
     */
    public function ls($pattern = NULL)
    {
        $outerIterator = ($pattern) ? $this->regex($this->rdi, $pattern) : $this->rdi;
        foreach($outerIterator as $obj){
            if ($obj->isDir()) {
                if ($obj->getFileName() == '..') {
                    continue;
                }
                $line = $obj->getPath() . PHP_EOL;
            } else {
                $line = sprintf('%4s %1d %4s %4s %10d %12s %-40s' . PHP_EOL,
                              substr(sprintf('%o', $obj->getPerms()), -4),
                              ($obj->getType() == 'file') ? 1 : 2,
                              $obj->getOwner(),
                              $obj->getGroup(),
                              $obj->getSize(),
                              date('M d Y H:i', $obj->getATime()),
                              $obj->getFileName());
            }
            yield $line;
        }
    }

    /**
     * Mimics the output of the "dir /s" command
     * chap_02_config.php chap_02_taking_advantage_of_ast.php home.php
     * chap_02_database_lookup_ast_example.php chap_02_understanding_ast_simple_example.php, etc.
     *
     * @param string $pattern
     * @return Generator
     */
    public function dir($pattern = NULL)
    {
        $outerIterator = ($pattern) ? $this->regex($this->rdi, $pattern) : $this->rdi;
        foreach($outerIterator as $name => $obj){
            yield $name . PHP_EOL;
        }
    }

    protected function regex($iterator, $pattern)
    {
        $pattern = '!^.' . str_replace('.', '\\.', $pattern) . '$!';
        return new RegexIterator($iterator, $pattern);
    }

}
