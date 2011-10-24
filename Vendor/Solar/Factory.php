<?php
/**
 * 
 * Abstract factory class to standardize adapter construction and return.
 * 
 * @category Solar
 * 
 * @package Solar
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Factory.php 4416 2010-02-23 19:52:43Z pmjones $
 * 
 */
abstract class Solar_Factory extends Solar_Base
{
    /**
     * 
     * Default configuration values.
     * 
     * @config string adapter The adapter class for the factory to generate.
     * 
     * @var array
     * 
     */
    protected $_Solar_Factory = array(
        'adapter' => null,
    );
    
    /**
     * 
     * Disallow all calls to methods besides factory() and the existing
     * support methods.
     * 
     * @param string $method The method called.
     * 
     * @param string $params Params for the method.
     * 
     * @return void
     * 
     */
    final public function __call($method, $params)
    {
        throw $this->_exception('ERR_METHOD_NOT_IMPLEMENTED', array(
            'method' => $method,
            'params' => $params,
        ));
    }
    
    /**
     * 
     * Factory method for returning adapter objects.
     * 
     * @return object
     * 
     */
    public function factory()
    {
        // bring in the config and get the adapter class.
        $config = $this->_config;
        $class = $config['adapter'];
        unset($config['adapter']);
        
        // return the factoried adapter object
        return new $class($config);
    }
}