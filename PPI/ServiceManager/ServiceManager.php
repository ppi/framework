<?php
/**
 * This file is part of the PPI Framework.
 *
 * @copyright   Copyright (c) 2012 Paul Dragoonis <paul@ppi.io>
 * @license     http://opensource.org/licenses/mit-license.php MIT
 * @link        http://www.ppi.io
 */

namespace PPI\ServiceManager;

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
class ServiceManager extends BaseServiceManager
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
}
