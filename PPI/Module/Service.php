<?php
/**
 * This file is part of the PPI Framework.
 *
 * @copyright  Copyright (c) 2012 Paul Dragoonis <paul@ppi.io>
 * @license    http://opensource.org/licenses/mit-license.php MIT
 * @link       http://www.ppi.io
 */
namespace PPI\Module;

/**
 * The PPI Server class. These instances can be registered into the
 * ServiceLocator.
 *
 * @package    PPI
 * @subpackage Module
 */
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

    /**
     * Constructor.
     *
     * @param type $className
     *
     * @return void
     */
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
     * @return boolean
     */
    public function hasClassName()
    {
        return $this->_className !== null;
    }

    /**
     * Set the factory method name
     *
     * @param string $method
     *
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
     * @return boolean
     */
    public function hasFactoryMethod()
    {
        return $this->_factoryMethod !== null;
    }

}
