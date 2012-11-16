<?php
/**
 * This file is part of the PPI Framework.
 *
 * @copyright  Copyright (c) 2012 Paul Dragoonis <paul@ppi.io>
 * @license    http://opensource.org/licenses/mit-license.php MIT
 * @link       http://www.ppi.io
 */
namespace PPI\Module;

use PPI\Config\ConfigLoader;
use PPI\Module\Routing\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Yaml\Yaml as YamlParser;

/**
 * The base PPI module class.
 *
 * @package    PPI
 * @subpackage Module
 */
abstract class Module implements ModuleInterface
{
    /**
     * @todo Add inline documentation.
     *
     * @var null
     */
    protected $_config = null;

    /**
     * Configuration loader.
     *
     * @var null|\PPI\Config\ConfigLoader
     */
    protected $configLoader = null;

    protected $reflected;

    /**
     * @todo Add inline documentation.
     *
     * @var null
     */
    protected $_routes = null;

    /**
     * @todo Add inline documentation.
     *
     * @var null
     */
    protected $_services = null;

    /**
     * @todo Add inline documentation.
     *
     * @var null
     */
    protected $_controller = null;

    /**
     * @todo Add inline documentation.
     *
     * @var null
     */
    protected $_moduleName = null;

    /**
     * Controller Name
     *
     * @var null
     */
    protected $_controllerName = null;

    /**
     * Action Name
     *
     * @var null
     */
    protected $_actionName = null;

    /**
     * Load up our routes
     *
     * @param type $path
     *
     * @return \Symfony\Component\Routing\RouteCollection
     */
    public function loadYamlRoutes($path)
    {
        if ($this->_routes === null) {
            $loader = new YamlFileLoader(new FileLocator(array(dirname($path))));
            $loader->setDefaults(array('_module' => $this->getModuleName()));

            $routesCollection = $loader->load(pathinfo($path, PATHINFO_FILENAME) . '.' . pathinfo($path, PATHINFO_EXTENSION));
            $this->_routes = $routesCollection;
        }

        return $this->_routes;
    }

    /**
     * Load up our config results from the specific yaml file.
     *
     * @param string $path
     *
     * @return array
     */
    public function loadYamlConfig($path)
    {
        if ($this->_config === null) {
            $parser = new YamlParser();
            $this->_config = $parser::parse($path);
        }

        return $this->_config;
    }

    /**
     * Set services for our module
     *
     * @param string $services
     *
     * @return Module
     */
    public function setServices($services)
    {
        $this->_services = $services;

        return $this;
    }

    /**
     * Get the services
     *
     * @return array
     */
    public function getServices()
    {
        return $this->_services;
    }

    /**
     * Get a particular service
     *
     * @param string $serviceName
     *
     * @return mixed
     */
    public function getService($serviceName)
    {
        return isset($this->_services[$serviceName]) ? $this->_services : null;
    }

    /**
     * Get the controller
     *
     * @return object
     */
    public function getController()
    {
        return $this->_controller;
    }

    /**
     * Set the controller
     *
     * @param object $controller
     *
     * @return Module
     */
    public function setController($controller)
    {
        $this->_controller = $controller;

        return $this;
    }

    /**
     * Check if a controller has been set
     *
     * @return boolean
     */
    public function hasController()
    {
        return $this->_controller !== null;
    }

    /**
     * @todo Add inline documentation.
     *
     * @param type $controllerName
     *
     * @return $this
     */
    public function setControllerName($controllerName)
    {
        $this->_controllerName = $controllerName;

        return $this;
    }

    /**
     * @todo Add inline documentation.
     *
     * @param type $actionName
     *
     * @return $this
     */
    public function setActionName($actionName)
    {
        $this->_actionName = $actionName;

        return $this;
    }

    /**
     * Dispatch process
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function dispatch()
    {
        if (!method_exists($this->_controller, $this->_actionName)) {
            throw new \Exception(sprintf(
                'Unable to dispatch action: "%s" does not exist in controller '.
                '"%s" within module "%s"',
                $this->_actionName,
                $this->_controllerName,
                $this->_moduleName
            ));
        }

        if (method_exists($this->_controller, 'preDispatch')) {
            $this->_controller->preDispatch();
        }

        $content = $this->_controller->{$this->_actionName}();

        if (method_exists($this->_controller, 'postDispatch')) {
            $this->_controller->postDispatch();
        }

        return $content;

    }

    /**
     * Returns a ConfigLoader instance.
     *
     * @return \PPI\Config\ConfigLoader
     */
    public function getConfigLoader()
    {
        if (null === $this->configLoader) {
            $this->configLoader = new ConfigLoader($this->getPath() . '/resources/config');
        }

        return $this->configLoader;
    }

    /**
     * Loads a configuration file or PHP array.
     *
     * @param mixed  $resource The resource
     * @param string $type     The resource type
     */
    public function loadConfig($resource, $type = null)
    {
        return $this->getConfigLoader()->load($resource, $type);
    }

    /**
     * Set the module name
     *
     * @param string $moduleName
     *
     * @return $this
     */
    public function setModuleName($moduleName)
    {
        $this->_moduleName = $moduleName;

        return $this;
    }

    /**
     * Get the module name
     *
     * @return string
     */
    public function getModuleName()
    {
        return $this->_moduleName;
    }

    /**
     * Gets the Module namespace.
     *
     * @return string The Module namespace
     *
     * @api
     */
    public function getNamespace()
    {
        if (null === $this->reflected) {
            $this->reflected = new \ReflectionObject($this);
        }

        return $this->reflected->getNamespaceName();
    }

    /**
     * Gets the Module directory path.
     *
     * @return string The Module absolute path
     *
     * @api
     */
    public function getPath()
    {
        if (null === $this->reflected) {
            $this->reflected = new \ReflectionObject($this);
        }

        return dirname($this->reflected->getFileName());
    }
}
