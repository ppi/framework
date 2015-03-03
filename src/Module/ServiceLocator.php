<?php
/**
 * This file is part of the PPI Framework.
 *
 * @copyright  Copyright (c) 2012 Paul Dragoonis <paul@ppi.io>
 * @license    http://opensource.org/licenses/mit-license.php MIT
 *
 * @link       http://www.ppi.io
 */

namespace PPI\Framework\Module;

/**
 * The PPI Service Locator.
 *
 * @author     Paul Dragoonis <paul@ppi.io>
 */
class ServiceLocator
{
    /**
     * @todo Add inline documentation.
     *
     * @var array
     */
    protected $loadedService = array();

    /**
     * @todo Add inline documentation.
     *
     * @var array
     */
    protected $services = array();

    /**
     * @todo Add inline documentation.
     *
     * @param array $services
     */
    public function __construct(array $services = array())
    {
        if (!empty($services)) {
            foreach ($services as $key => $service) {
                $this->services[strtolower($key)] = $service;
            }
        }
    }

    /**
     * Get a registered service by its name.
     *
     * @param string $key
     *
     * @throws \Exception|\InvalidArgumentException
     *
     * @return mixed
     */
    public function get($key)
    {
        $key = strtolower($key);

        if (!isset($this->services[$key])) {
            throw new \InvalidArgumentException('Service not found: ' . $key);
        }

        // Have we been here before?
        if (isset($this->loadedService[$key])) {
            return $this->loadedService[$key];
        }

        if (!$this->services[$key] instanceof Service) {
            $this->loadedService[$key] = $this->services[$key];

            return $this->loadedService[$key];
        }

        // It's a Service instance, lets do some extra stuff.
        if (!$this->services[$key]->hasClassName()) {
            throw new \Exception('Unable to find class name from definition: ' . $key);
        }

        $className = $this->services[$key]->getClassName();
        $instance  = new $className();

        if ($this->services[$key]->hasFactoryMethod()) {
            call_user_func($instance, $this->services[$key]->getFactoryMethod());
        }

        $this->loadedService[$key] = $instance;

        return $this->loadedService[$key];
    }

    /**
     * Set a service.
     *
     * @param string $key
     * @param mixed  $service
     */
    public function set($key, $service)
    {
        $this->services[strtolower($key)] = $service;
    }

    /**
     * Check if a service has been registered.
     *
     * @param string $key
     *
     * @return boolean
     */
    public function has($key)
    {
        return isset($this->services[strtolower($key)]);
    }

    /**
     * @todo Add inline documentation.
     *
     * @return array
     */
    public function getKeys()
    {
        return array_keys($this->services);
    }
}
