<?php
/**
 * @author    Paul Dragoonis (dragoonis@php.net)
 * @license   http://opensource.org/licenses/mit-license.php MIT
 * @package   Core
 * @link      wwww.ppi.io
 */
namespace PPI\Exception;
class Log implements ExceptionInterface {
	
	/**
	 * Error log
	 *
	 * @var null|string
	 */
	protected $_log = null;
	
	/**
	 * Write the Exception to a log file
	 * 
	 * @param \Exception
	 */
	public function handle(\Exception $e) {
		
		if(($logFile = ini_get('error_log')) !== null && is_writable($logFile)) {
			$date = '['. date('D M d H:i:s Y') .'] ';
			$message = $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine() . PHP_EOL;
			return file_put_contents($logFile, $date . $message , FILE_APPEND|LOCK_EX) > 0;
		} else {
			// We can stop execution here if required, for now just return false
			return false;
		}
	}
}
