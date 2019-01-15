<?php
namespace Application\Error;

class Handler
{

    protected $logFile;

	public function __construct($logFileDir = NULL, $logFile = NULL)
	{
		$logFile    = $logFile    ?? date('Ymd') . '.log';
		$logFileDir = $logFileDir ?? __DIR__;
		$this->logFile = $logFileDir . '/' . $logFile;
		$this->logFile = str_replace('//', '/', $this->logFile);
		set_exception_handler([$this,'exceptionHandler']);
		set_error_handler([$this, 'errorHandler']);
    }

	public function errorHandler($errno, $errstr, $errfile, $errline)
	{
		$message = sprintf('ERROR    : %s : %d : %s : %s : %s' . PHP_EOL,
			date('Y-m-d H:i:s'), $errno, $errstr, $errfile, $errline);
		file_put_contents($this->logFile, $message, FILE_APPEND);
	}

	public function exceptionHandler($ex)
	{
		$message = sprintf('EXCEPTION: %19s : %20s : %s' . PHP_EOL,
			date('Y-m-d H:i:s'), get_class($ex), $ex->getMessage());
		file_put_contents($this->logFile, $message, FILE_APPEND);
	}

}
