<?php
/**
 * This file is part of the PPI Framework.
 *
 * @copyright  Copyright (c) 2011-2013 Paul Dragoonis <paul@ppi.io>
 * @license    http://opensource.org/licenses/mit-license.php MIT
 * @link       http://www.ppi.io
 */

namespace PPI\Exception;

/**
 * Log class
 *
 * @todo Add inline documentation.
 *
 * @package    PPI
 * @subpackage Exception
 */
class Log implements HandlerInterface
{
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
     * Set the log listener options
     *
     * @param array $options
     *
     * @return void
     */
    public function __construct(array $options = array())
    {
        if (isset($options['logFile'])) {
            $this->_logFile = $options['logFile'];
        }
    }

    /**
     * Set the log file
     *
     * @param string $logFile
     *
     * @return void
     */
    public function setLogFile($logFile)
    {
        if (is_string($logFile)) {
            $this->_logFile = $logFile;
        }
    }

    /**
     * Get the log file path
     *
     * @return mixed
     */
    private function getLogFile()
    {
        return (isset($this->_logFile)) ? $this->_logFile : ini_get('error_log');
    }

    /**
     * Write the Exception to a log file
     *
     * @param \Exception $e
     *
     * @return array
     */
    public function handle(\Exception $e)
    {
        $logFile = $this->getLogFile();

        if (is_writable($logFile)) {
            $logEntry = sprintf(
                '[%s] %s in %s on line %s' . PHP_EOL,
                date($this->_dateFormat),
                $e->getMessage(),
                $e->getFile(),
                $e->getLine()
            );

            if (file_put_contents($logFile, $logEntry , FILE_APPEND|LOCK_EX) > 0) {
                return array(
                    'status'  => true,
                    'message' => 'Written to log file ' . $logFile
                );
            }
        }

        return array(
            'status'  => false,
            'message' => 'Unable to write to log file ' . $logFile
        );
    }

}
