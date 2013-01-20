<?php
/**
 * This file is part of the PPI Framework.
 *
 * @copyright   Copyright (c) 2011-2013 Paul Dragoonis <paul@ppi.io>
 * @license     http://opensource.org/licenses/mit-license.php MIT
 * @link        http://www.ppi.io
 */
namespace PPI\ServiceManager\Config;

use Zend\ServiceManager\Config;
use Zend\ServiceManager\ServiceManager;

/**
 * AbstractConfig class.
 *
 * @author     Vítor Brandão <vitor@ppi.io>
 * @package    PPI
 * @subpackage ServiceManager
 */
abstract class AbstractConfig extends Config
{
    /**
     * Returns the mandatory prefix to use when using YAML.
     *
     * This convention is to remove the "Config" postfix from the class
     * name and then lowercase and underscore the result. So:
     *
     *     AcmeHelloConfig
     *
     * becomes
     *
     *     acme_hello
     *
     * This can be overridden in a sub-class to specify the alias manually.
     *
     * @return string The alias
     *
     * @throws \BadMethodCallException When the extension name does not follow conventions
     */
    public function getAlias()
    {
        $className = get_class($this);
        if (substr($className, -6) != 'Config') {
            throw new \BadMethodCallException('This Config class does not follow the naming convention; you must overwrite the getAlias() method.');
        }
        $classBaseName = substr(strrchr($className, '\\'), 1, -6);

        return strtolower($classBaseName);
    }

    /**
     * Returns the configuration reference for this component.
     *
     * @returns array
     */
    public function getConfigurationReference()
    {
        return array($this->getAlias() => array());
    }

    /**
     * Processes an array of configurations.
     *
     * @param ServiceManager $serviceManager The Service Manager
     * @param array          $configs        An array of configuration items to process
     *
     * @return array The processed configuration
     */
    protected function processConfiguration(ServiceManager $serviceManager, array $configs)
    {
        $alias = $this->getAlias();

        return isset($configs[$alias]) ? $configs[$alias] : array();
    }
}
