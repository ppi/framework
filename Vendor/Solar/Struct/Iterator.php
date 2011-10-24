<?php
/**
 * 
 * Support class to provide an iterator for Solar_Struct objects.
 * 
 * Note that this class does not extend Solar_Base; its only purpose is to
 * implement the Iterator interface as lightly as possible.
 * 
 * @category Solar
 * 
 * @package Solar
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Iterator.php 4272 2009-12-17 21:56:32Z pmjones $
 * 
 */
class Solar_Struct_Iterator implements Iterator
{
    /**
     * 
     * The struct over which we are iterating.
     * 
     * @var Solar_Struct
     * 
     */
    protected $_struct = array();
    
    /**
     * 
     * Is the current iterator position valid?
     * 
     * @var bool
     * 
     */
    protected $_valid = false;
    
    /**
     * 
     * The list of all keys in the struct.
     * 
     * @var bool
     * 
     */
    protected $_keys = array();
    
    /**
     * 
     * Constructor; note that this is **not** a Solar constructor.
     * 
     * @param Solar_Struct $struct The struct for which this iterator will be
     * used.
     * 
     */
    public function __construct(Solar_Struct $struct)
    {
        $this->_struct = $struct;
        $this->_keys   = $struct->getKeys();
    }
    
    /**
     * 
     * Returns the struct value for the current iterator position.
     * 
     * @return mixed
     * 
     */
    public function current()
    {
        return $this->_struct->__get($this->key());
    }
    
    /**
     * 
     * Returns the current iterator position.
     * 
     * @return mixed
     * 
     */
    public function key()
    {
        return current($this->_keys);
    }
    
    /**
     * 
     * Moves the iterator to the next position.
     * 
     * @return void
     * 
     */
    public function next()
    {
        $this->_valid = (next($this->_keys) !== false);
    }
    
    /**
     * 
     * Moves the iterator to the first position.
     * 
     * @return void
     * 
     */
    public function rewind()
    {
        $this->_valid = (reset($this->_keys) !== false);
    }
    
    /**
     * 
     * Is the current iterator position valid?
     * 
     * @return void
     * 
     */
    public function valid()
    {
        return $this->_valid;
    }
}
