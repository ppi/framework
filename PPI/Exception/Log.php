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
	 * Error log file
	 *
	 * @var null|string
	 */
	protected $_logFile = null;
	
	/**
	 * Date format
	 * 
	 * @var string
	 */
	protected $_dateFormat = 'D M d H:i:s Y';
		
	/**
	 * Write the Exception to a log file
	 * 
	 * @param \Exception
	 */
	public function handle(\Exception $e) {
		
		$this->_logFile = ini_get('error_log');
		if($this->_logFile !== null && is_writable($this->_logFile)) {
			$date = '['. date($this->_dateFormat) .'] ';
			$message = $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine() . PHP_EOL;
			return file_put_contents($this->_logFile, $date . $message , FILE_APPEND|LOCK_EX) > 0;
		} else {
			// We can stop execution here if required, for now just return false
			return false;
		}
	}
}
