<?php
/**
 * The PPI Service class. These instances can be registered into the ServiceLocator
 *
 * @package   Core
 * @author    Paul Dragoonis <dragoonis@php.net>
 * @license   http://opensource.org/licenses/mit-license.php MIT
 * @link      http://www.ppi.io
 */
namespace PPI\Module;

class Service
{
    /**
     * The class name for this service
     *
     * @var null|string
     */
    protected $_className = null;

    /**
     * Get the factory method name for this service
     *
     * @var null|string
     */
    protected $_factoryMethod = null;

    public function __construct($className = null)
    {
        $this->_className = $className;

    }

    /**
     * Get the class name for this service
     *
     * @return null|string
     */
    public function getClassName()
    {
        return $this->_className;
    }

    /**
     * Check if we have a class name for this service
     *
     * @return bool
     */
    public function hasClassName()
    {
        return $this->_className !== null;
    }

    /**
     * Set the factory method name
     *
     * @param  string $method
     * @return void
     */
    public function setFactoryMethod($method)
    {
        $this->_factoryMethod = $method;
    }

    /**
     * Get the factory method name
     *
     * @return null|string
     */
    public function getFactoryMethod()
    {
        return $this->_factoryMethod;
    }

    /**
     * Check if we have a factory method name
     *
     * @return bool
     */
    public function hasFactoryMethod()
    {
        return $this->_factoryMethod !== null;
    }

}
