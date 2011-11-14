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
	* Set the log file
	* 
	* @param string $logFile
	*/
	public function setLogFile($logFile) {
		$this->_logFile = $logFile;
	}
	
	/**
	 * Get the log file path
	 * 
	 * @return mixed
	 */
	private function _getLogFile() {
		return (isset($this->_logFile)) ? $this->_logFile : ini_get('error_log');
	}
	
		
	/**
	 * Write the Exception to a log file
	 * 
	 * @param \Exception
	 */
	public function handle(\Exception $e) {
		
		$logFile = $this->_getLogFile();
		if(is_writable($logFile)){
			$logEntry  = '[' . date($this->_dateFormat) . '] ' . $e->getMessage();
			$logEntry .= ' in ' . $e->getFile() . ' on line ' . $e->getLine() . PHP_EOL;
			if(file_put_contents($logFile, $logEntry , FILE_APPEND|LOCK_EX) > 0){
				return array('status' => true, 'message' => 'Written to log file ' . $logFile);
			}
		}
		return array('status' => false, 'message' => 'Unable to write to log file ' . $this->logFile);
	}
}
