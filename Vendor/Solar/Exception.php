<?php
/**
 * 
 * Generic exception class.
 * 
 * @category Solar
 * 
 * @package Solar
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Exception.php 3850 2009-06-24 20:18:27Z pmjones $
 * 
 */
class Solar_Exception extends Exception
{
    /**
     * 
     * User-defined information array.
     * 
     * @var array
     * 
     */
    protected $_info = array();
    
    /**
     * 
     * Class where the exception originated.
     * 
     * @var array
     * 
     */
    protected $_class;
    
    /**
     * 
     * Constructor.
     * 
     * @param array $config Configuration value overrides, if any.
     * for 'class', 'code', 'text', and 'info'.
     * 
     */
    public function __construct($config = null)
    {
        $default = array(
            'class' => '',
            'code'  => '',
            'text'  => '',
            'info'  => array(),
        );
        $config = array_merge($default, (array) $config);
        
        parent::__construct($config['text']);
        $this->code = $config['code'];
        $this->_class = $config['class'];
        $this->_info = (array) $config['info'];
    }
    
    /**
     * 
     * Returnes the exception as a string.
     * 
     * @return void
     * 
     */
    public function __toString()
    {
        $class_code = $this->_class . "::" . $this->code;
        
        // basic string
        $str = "exception '" . get_class($this) . "'\n"
             . "class::code '$class_code' \n"
             . "with message '" . $this->message . "' \n"
             . "information " . var_export($this->_info, true) . " \n"
             . "Stack trace:\n"
             . "  " . str_replace("\n", "\n  ", $this->getTraceAsString());
        
        // at the CLI, repeat the message so it shows up as the last line
        // of output, not the trace.
        if (PHP_SAPI == 'cli') {
            $str .= "\n\n{$this->message}";
        }
        
        // done
        return $str;
    }
    
    /**
     * 
     * Returns user-defined information.
     * 
     * @param string $key A particular info key to return; if empty, returns
     * all info.
     * 
     * @return array
     * 
     */
    final public function getInfo($key = null)
    {
        if (empty($key)) {
            return $this->_info;
        } else {
            return $this->_info[$key];
        }
    }
    
    /**
     * 
     * Returns the class name that threw the exception.
     * 
     * @return string
     * 
     */
    final public function getClass()
    {
        return $this->_class;
    }
    
    /**
     * 
     * Returns the class name and code together.
     * 
     * @return string
     * 
     */
    final public function getClassCode()
    {
        return $this->_class . '::' . $this->code;
    }
}
