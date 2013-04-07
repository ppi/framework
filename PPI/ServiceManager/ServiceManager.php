<?php
/**
 * This file is part of the PPI Framework.
 *
 * @copyright   Copyright (c) 2011-2013 Paul Dragoonis <paul@ppi.io>
 * @license     http://opensource.org/licenses/mit-license.php MIT
 * @link        http://www.ppi.io
 */

namespace PPI\ServiceManager;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ScopeInterface;
use Zend\ServiceManager\ConfigInterface;
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
 * @author     Vítor Brandão <vitor@ppi.io>
 * @package    PPI
 * @subpackage ServiceManager
 */
class ServiceManager extends BaseServiceManager implements ContainerInterface
{
    public function __construct(ConfigInterface $config = null)
    {
        parent::__construct($config);

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
     * @param string  $name
     * @param boolean $usePeeringServiceManagers
     *
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
     * @param string  $name
     * @param mixed   $service
     * @param boolean $shared
     *
     * @return ServiceManager
     */
    public function set($name, $service, $shared = true)
    {
        return $this->setService($name, $service, $shared);
    }

    /**
     * Gets a parameter.
     *
     * @param string $name The parameter name
     *
     * @return mixed The parameter value
     *
     * @throws InvalidArgumentException if the parameter is not defined
     */
    public function getParameter($name)
    {
        $config = $this->get('Config');
        if (!isset($config['parameters'][$name])) {
            throw new \InvalidArgumentException(sprintf('You have requested a non-existent parameter "%s".', $name));
        }

        return $config['parameters'][$name];
    }

    /**
     * Checks if a parameter exists.
     *
     * @param string $name The parameter name
     *
     * @return Boolean The presence of parameter in container
     *
     * @api
     */
    public function hasParameter($name)
    {
        $config = $this->get('Config');

        return isset($config['parameters'][$name]);
    }

    /**
     * Sets a parameter.
     *
     * @param string $name  The parameter name
     * @param mixed  $value The parameter value
     */
    public function setParameter($name, $value)
    {
        $config = $this->get('Config');
        $config['parameters'][$name] = $value;

        $this->set('Config', $config);
    }

    /**
     * Retrieve a keyed list of all registered services. Handy for debugging!
     *
     * @return array
     */
    public function getRegisteredServicesReal()
    {
        return array(
            'invokableClasses' => $this->invokableClasses,
            'factories' => $this->factories,
            'aliases' => $this->aliases,
            'instances' => $this->instances,
        );
    }

    public function enterScope($name)
    {
        throw new NotImplementedExceptionException();
    }

    public function leaveScope($name)
    {
        throw new NotImplementedExceptionException();
    }

    public function addScope(ScopeInterface $scope)
    {
        throw new NotImplementedExceptionException();
    }

    public function hasScope($name)
    {
        throw new NotImplementedExceptionException();
    }

    public function isScopeActive($name)
    {
        throw new NotImplementedExceptionException();
    }
}
