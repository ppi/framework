<?php

/**
 * The base PPI module class.
 *
 * @package   Core
 * @author    Paul Dragoonis <dragoonis@php.net>
 * @license   http://opensource.org/licenses/mit-license.php MIT
 * @link      http://www.ppi.io
 */

namespace PPI\Module;

use PPI\Module\Routing\Loader\YamlFileLoader,
    Symfony\Component\Config\FileLocator,
    Symfony\Component\Yaml\Yaml as YamlParser;

class Module
{
    /**
     * @var null
     */
    protected $_config = null;

    /**
     * @var null
     */
    protected $_routes = null;
    /**
     * @var null
     */
    protected $_services = null;

    /**
     * @var null
     */
    protected $_controller = null;

    /**
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

    public function __construct()
    {
    }

    /**
     * Load up our routes
     *
     * @param $path
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
     * @param  string $path
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
     * @param  string $services
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
     * @param  string $serviceName
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
     * @param  object $controller
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
     * @return bool
     */
    public function hasController()
    {
        return $this->_controller !== null;
    }

    public function setControllerName($controllerName)
    {
        $this->_controllerName = $controllerName;

        return $this;
    }

    public function setActionName($actionName)
    {
        $this->_actionName = $actionName;

        return $this;
    }

    /**
     * Dispatch process
     *
     * @return mixed
     * @throws \Exception
     */
    public function dispatch()
    {
        if (!method_exists($this->_controller, $this->_actionName)) {
            throw new \Exception('Unable to dispatch action: '
                . $this->_actionName . ' does not exist in controller: ' . $this->_controllerName . ' within module: '
                . $this->_moduleName);
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
     * Set the module name
     *
     * @param string $moduleName
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

}
