<?php
/**
 * The PPI Service Locator
 *
 * @package   Core
 * @author    Paul Dragoonis <dragoonis@php.net>
 * @license   http://opensource.org/licenses/mit-license.php MIT
 * @link      http://www.ppi.io
 */
namespace PPI\Module;

use PPI\Module\Service;

class ServiceLocator
{
    protected $_loadedService = array();
    protected $_services = array();

    public function __construct(array $services = array())
    {
        if (!empty($services)) {
            foreach ($services as $key => $service) {
                $this->_services[strtolower($key)] = $service;
            }
        }

    }

    /**
     * Get a registered service by its name
     *
     * @param  string                               $key
     * @return mixed
     * @throws \Exception|\InvalidArgumentException
     */
    public function get($key)
    {
        $key = strtolower($key);
        if (!isset($this->_services[$key])) {
            throw new \InvalidArgumentException('Service not found: ' . $key);
        }

        // Have we been here before?
        if (isset($this->_loadedService[$key])) {
            return $this->_loadedService[$key];
        }

        if (!$this->_services[$key] instanceof Service) {
            $this->_loadedService[$key] = $this->_services[$key];

            return $this->_loadedService[$key];
        }

        // It's a Service instance, lets do some extra stuff.
        if (!$this->_services[$key]->hasClassName()) {
            throw new \Exception('Unable to find class name from definition: ' . $key);
        }

        $className = $this->_services[$key]->getClassName();
        $instance = new $className;

        if ($this->_services[$key]->hasFactoryMethod()) {
            call_user_func($instance, $this->_services[$key]->getFactoryMethod());
        }

        $this->_loadedService[$key] = $instance;

        return $this->_loadedService[$key];
    }

    /**
     * Set a service
     *
     * @param string $key
     * @param mixed  $service
     */
    public function set($key, $service)
    {
        $key = strtolower($key);

        $this->_services[$key] = $service;
    }

    /**
     * Check if a service has been registered
     *
     * @param  string $key
     * @return bool
     */
    public function has($key)
    {
        $key = strtolower($key);

        return isset($this->_services[$key]);
    }

    /**
     *
     *
     * @return array
     */
    public function getKeys()
    {
        return array_keys($this->_services);
    }

}
