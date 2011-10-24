<?php
/**
 * @version   1.0
 * @author    Paul Dragoonis <dragoonis@php.net>
 * @license   http://opensource.org/licenses/mit-license.php MIT
 * @copyright Digiflex Development
 * @package   Config
 *
 */
namespace PPI\Config;
class Generic implements \Countable, \Iterator {

    /**
     * Iteration index
     *
     * @var integer $_index
     */
    protected $_index;

    /**
     * Number of elements in configuration data
     *
     * @var integer $_count
     */
    protected $_count;

    /**
     * Contains array of configuration data
     *
     * @var array $_data
     */
    protected $_data;


    /**
     * Contains which config file sections were loaded. This is null
     * if all sections were loaded, a string name if one section is loaded
     * and an array of string names if multiple sections were loaded.
     *
     * @var mixed $_loadedSection
     */
    protected $_loadedSection;



    /**
     * PPI_Config provides a property based interface to
     * an array. The data are read-only unless $allowModifications
     * is set to true on construction.
     *
     * PPI_Config also implements Countable and Iterator to
     * facilitate easy access to the data.
     *
     * @param  array $array
     * @return void
     */
    public function __construct(array $array) {
        $this->_loadedSection = null;
        $this->_index = 0;
        $this->_data = array();
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $this->_data[$key] = new self($value);
            } else {
                $this->_data[$key] = $value;
            }
        }
        $this->_count = count($this->_data);
    }

    /**
     * Retrieve a value and return $default if there is no element set.
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function get($name, $default = null) {
        $result = $default;
        if (array_key_exists($name, $this->_data)) {
            $result = $this->_data[$name];
        }
        return $result;
    }

    /**
     * Magic function so that $obj->value will work.
     *
     * @param string $name
     * @return mixed
     */
    public function __get($name) {
        return $this->get($name);
    }

    /**
     * Only allow setting of a property if $allowModifications
     * was set to true on construction. Otherwise, throw an exception.
     *
     * @param  string $name
     * @param  mixed  $value
     * @throws PPI_Exception
     * @return void
     */
    public function __set($name, $value) {
		throw new PPI_Exception('PPI_Config is read only');
    }

    /**
     * Deep clone of this instance to ensure that nested PPI_Configs
     * are also cloned.
     *
     * @return void
     */
    public function __clone() {
      $array = array();
      foreach ($this->_data as $key => $value) {
          if ($value instanceof PPI_Config) {
              $array[$key] = clone $value;
          } else {
              $array[$key] = $value;
          }
      }
      $this->_data = $array;
    }

    /**
     * Return an associative array of the stored data.
     *
     * @return array
     */
    public function toArray() {
        $array = array();
        foreach ($this->_data as $key => $value) {
            if ($value instanceof PPI_Config) {
                $array[$key] = $value->toArray();
            } else {
                $array[$key] = $value;
            }
        }
        return $array;
    }

    /**
     * Support isset() overloading on PHP 5.1
     *
     * @param string $name
     * @return boolean
     */
    public function __isset($name) {
        return isset($this->_data[$name]);
    }

    /**
     * Support unset() overloading on PHP 5.1
     *
     * @param  string $name
     * @throws PPI_Exception
     * @return void
     */
    public function __unset($name) {
		/** @see PPI_Exception */
		throw new PPI_Exception('PPI_Config is read only');

    }

    /**
     * Defined by Countable interface
     *
     * @return int
     */
    public function count() {
        return $this->_count;
    }

    /**
     * Defined by Iterator interface
     *
     * @return mixed
     */
    public function current() {
        return current($this->_data);
    }

    /**
     * Defined by Iterator interface
     *
     * @return mixed
     */
    public function key() {
        return key($this->_data);
    }

    /**
     * Defined by Iterator interface
     *
     */
    public function next() {
        next($this->_data);
        $this->_index++;
    }

    /**
     * Defined by Iterator interface
     *
     */
    public function rewind() {
        reset($this->_data);
        $this->_index = 0;
    }

    /**
     * Defined by Iterator interface
     *
     * @return boolean
     */
    public function valid() {
        return $this->_index < $this->_count;
    }

    /**
     * Returns the section name(s) loaded.
     *
     * @return mixed
     */
    public function getSectionName() {
        return $this->_loadedSection;
    }

    /**
     * Returns true if all sections were loaded
     *
     * @return boolean
     */
    public function areAllSectionsLoaded() {
        return $this->_loadedSection === null;
    }



    /**
     * Handle any errors from simplexml_load_file or parse_ini_file
     *
     * @param integer $errno
     * @param string $errstr
     * @param string $errfile
     * @param integer $errline
     */
    protected function _loadFileErrorHandler($errno, $errstr, $errfile, $errline) {
        if ($this->_loadFileErrorStr === null) {
            $this->_loadFileErrorStr = $errstr;
        } else {
            $this->_loadFileErrorStr .= (PHP_EOL . $errstr);
        }
    }

}
