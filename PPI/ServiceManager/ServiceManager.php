<?php
/**
 * This file is part of the PPI Framework.
 *
 * @category    PPI
 * @package     ServiceManager
 * @copyright   Copyright (c) 2012 Paul Dragoonis <paul@ppi.io>
 * @license     http://opensource.org/licenses/mit-license.php MIT
 * @link        http://www.ppi.io
 */

namespace PPI\ServiceManager;

use PPI\ServiceManager\Options\OptionsInterface;
use Zend\ServiceManager\ServiceManager as BaseServiceManager;

/**
 * ServiceManager implements the Service Locator design pattern.
 *
 * The Service Locator is a service/object locator, tasked with retrieving other
 * objects. We borrow this one from Zend Framework 2.
 *
 * External documentation for ServiceManager:
 * * http://packages.zendframework.com/docs/latest/manual/en/modules/zend.service-manager.intro.html
 * * http://packages.zendframework.com/docs/latest/manual/en/modules/zend.service-manager.quick-start.html
 * * http://blog.evan.pro/introduction-to-the-zend-framework-2-servicemanager
 * * https://github.com/zendframework/zf2/blob/master/library/Zend/ServiceManager/ServiceManager.php
 *
 * Options keys are case insensitive.
 *
 * @author Vítor Brandão <vitor@ppi.io>
 */
class ServiceManager extends BaseServiceManager implements \ArrayAccess, \IteratorAggregate, \Countable
{
    protected $options;

    /**
     * ServiceManager constructor.
     *
     * @param OptionsInterface $options Application options
     * @param array            $configs Array of ConfigInterface instances
     */
    public function __construct(OptionsInterface $options, array $configs = array())
    {
        $this->options = $options;

        foreach ($configs as $config) {
            $config->configureServiceManager($this);
        }

        $this->set('servicemanager', $this);

        /**
         * @note Unfortunately we need this to allow 'response' key to be overridden.
         * Hopefully in a later version we can refactor and break Backwards
         * Compatibility and thus disable this feature.
         */
        $this->setAllowOverride(true);
    }

    /**
     * Retrieve a registered instance
     *
     * This method is an alias to $this->get().
     *
     * @param  string       $cName
     * @param  bool         $usePeeringServiceManagers
     * @return object|array
     */
    public function getService($name, $usePeeringServiceManagers = true)
    {
        return $this->get($name, $usePeeringServiceManagers);
    }

    /**
     * Register a service with the locator.
     *
     * This method is an alias to $this->setService().
     *
     * @param  string                                $name
     * @param  mixed                                 $service
     * @param  bool                                  $shared
     * @return ServiceManager
     * @throws Exception\InvalidServiceNameException
     */
    public function set($name, $service, $shared = true)
    {
        return $this->setService($name, $service, $shared);
    }

    /**
     * Compiles ServiceManager options.
     *
     * This method does one thing:
     *
     *  * Parameter values are resolved;
     */
    public function compile()
    {
        $this->options->resolve();
    }

    /**
     * Returns the OptionsInterface instance used to store app parameters.
     *
     * @return OptionsInterface
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Gets an option.
     *
     * @param string $name The option name
     *
     * @return mixed The option value
     *
     * @throws InvalidArgumentException if the option is not defined
     */
    public function getOption($name)
    {
        return $this->options->get($name);
    }

    /**
     * Checks if an option exists.
     *
     * @param string $name The option name
     *
     * @return Boolean The presence of option in container
     */
    public function hasOption($name)
    {
        return $this->options->has($name);
    }

    /**
     * Sets an option.
     *
     * @param string $name  The option name
     * @param mixed  $value The option value
     */
    public function setOption($name, $value)
    {
        $this->options->set($name, $value);
    }

    /**
     * Equivalent to {@link has()}.
     *
     * @param string $option The option name.
     *
     * @return Boolean Whether the option exists.
     *
     * @see \ArrayAccess::offsetExists()
     */
    public function offsetExists($option)
    {
        return $this->options->has($option);
    }

    /**
     * Equivalent to {@link get()}.
     *
     * @param string $option The option name.
     *
     * @return mixed The option value.
     *
     * @throws \OutOfBoundsException     If the option does not exist.
     * @throws OptionDefinitionException If a cyclic dependency is detected
     *                                   between two lazy options.
     *
     * @see \ArrayAccess::offsetGet()
     */
    public function offsetGet($option)
    {
        return $this->options->get($option);
    }

    /**
     * Equivalent to {@link set()}.
     *
     * @param string $option The name of the option.
     * @param mixed  $value  The value of the option. May be a closure with a
     *                       signature as defined in DefaultOptions::add().
     *
     * @throws OptionDefinitionException If options have already been read.
     *                                   Once options are read, the container
     *                                   becomes immutable.
     *
     * @see \ArrayAccess::offsetSet()
     */
    public function offsetSet($option, $value)
    {
        $this->options->set($option, $value);
    }

    /**
     * Equivalent to {@link remove()}.
     *
     * @param string $option The option name.
     *
     * @throws OptionDefinitionException If options have already been read.
     *                                   Once options are read, the container
     *                                   becomes immutable.
     *
     * @see \ArrayAccess::offsetUnset()
     */
    public function offsetUnset($option)
    {
        $this->options->remove($option);
    }

   /**
     * Returns an iterator for parameters.
     *
     * @return \ArrayIterator An \ArrayIterator instance
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->options->all());
    }

    /**
     * Returns the number of parameters.
     *
     * @return int The number of parameters
     */
    public function count()
    {
        return count($this->options->all());
    }
}
