<?php
/**
 * 
 * A value-object to represent the various parameters for specifying a model
 * fetch() call.
 * 
 * @category Solar
 * 
 * @package Solar_Sql_Model
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Params.php 4533 2010-04-23 16:35:15Z pmjones $
 * 
 */
abstract class Solar_Sql_Model_Params extends Solar_Struct { 
    
    /**
     * 
     * Default data array.
     * 
     * @var array
     * 
     */
    protected $_data = array(
        'cols'  => array(),
        'eager' => array(),
        'alias' => null,
    );
    
    /**
     * 
     * Performs a "deep" clone of objects in the data.
     * 
     * @return void
     * 
     */
    public function __clone()
    {
        // do a "deep" clone of the objects
        foreach ($this->_data['eager'] as $name => $eager) {
            $clone = clone($eager);
            $this->_data[$name] = $clone;
        }
    }
    
    /**
     * 
     * Adds new columns to the existing list of columns.
     * 
     * @param array $list The new columns to add to the existing ones.
     * 
     * @return Solar_Sql_Model_Params
     * 
     */
    public function cols($list)
    {
        $list = array_merge(
            (array) $this->_data['cols'],
            (array) $list
        );
        
        $this->_data['cols'] = array_unique($list);
        return $this;
    }
    
    /**
     * 
     * Adds a new related eager-fetch (with options) to the params.
     * 
     * @param string $name The name of the related to eager-fetch.
     * 
     * @param array $opts Options for the eager-fetch; cf.
     * [[Solar_Sql_Model_Params_Eager]].
     * 
     * @return Solar_Sql_Model_Params
     * 
     */
    public function eager($name, $opts = null)
    {
        // BC-helping logic
        if (is_int($name) && is_string($opts)) {
            $name = $opts;
            $opts = null;
        }
        
        // now the real logic
        if (empty($this->_data['eager'][$name])) {
            $eager = Solar::factory('Solar_Sql_Model_Params_Eager');
            $this->_data['eager'][$name] = $eager;
        }
        
        $this->_data['eager'][$name]->load($opts);
        return $this;
    }
    
    /**
     * 
     * Sets the alias to use for this eager or fetch.
     * 
     * @param string $val The alias name.
     * 
     * @return Solar_Sql_Model_Params
     * 
     */
    public function alias($val)
    {
        $this->_data['alias'] = (string) $val;
    }
    
    /**
     * 
     * Loads this params object with an array or struct.
     * 
     * @param array|Solar_Struct $spec The data to load.
     * 
     * @return Solar_Sql_Model_Params
     * 
     * @see _load()
     * 
     */
    public function load($spec)
    {
        parent::load($spec);
        return $this;
    }
    
    /**
     * 
     * Loads this params object with an array of data using support methods.
     * 
     * @param array $data The data to load.
     * 
     * @return Solar_Sql_Model_Params
     * 
     * @see _loadOne()
     * 
     * @see _loadTwo()
     * 
     */
    protected function _load($data)
    {
        $this->_loadOne($data, array('cols', 'alias'));
        $this->_loadTwo($data, array('eager'));
    }
    
    /**
     * 
     * Calls one-argment methods to load $data elements.
     * 
     * @param array $data The data to load.
     * 
     * @param array $list Which data elements to load using one-argument
     * methods.
     * 
     * @return void
     * 
     */
    protected function _loadOne($data, $list)
    {
        foreach ($list as $prop => $func) {
            if (is_int($prop)) {
                $prop = $func;
            }
            if (array_key_exists($prop, $data)) {
                foreach ((array) $data[$prop] as $val) {
                    $this->$func($val);
                }
            }
        }
    }
    
    /**
     * 
     * Calls two-argment methods to load $data elements.
     * 
     * @param array $data The data to load.
     * 
     * @param array $list Which data elements to load using two-argument
     * methods.
     * 
     * @return void
     * 
     */
    protected function _loadTwo($data, $list)
    {
        foreach ($list as $prop => $func) {
            if (is_int($prop)) {
                $prop = $func;
            }
            if (array_key_exists($prop, $data)) {
                foreach ((array) $data[$prop] as $key => $val) {
                    $this->$func($key, $val);
                }
            }
        }
    }
}
