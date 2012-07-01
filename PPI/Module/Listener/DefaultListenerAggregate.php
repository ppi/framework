<?php
namespace PPI\Module\Listener;

use
    Zend\ModuleManager\Listener\DefaultListenerAggregate as ZendDefaultListenerAggregate,
    Zend\EventManager\EventManagerInterface,
    Zend\ModuleManager\ModuleEvent;

class DefaultListenerAggregate extends ZendDefaultListenerAggregate
{
    /**
     * The routes registered for our
     *
     * @var array
     */
    protected $_routes = array();

    /**
     * Services for the ServiceLocator
     *
     * @var array
     */
    protected $_services = array();

    /**
     * Override of attach(). Customising the events to be triggered upon the 'loadModule' event.
     *
     * @param  \Zend\EventManager\EventManagerInterface $events
     * @return DefaultListenerAggregate
     */
    public function attach(EventManagerInterface $events)
    {
        parent::attach($events);

        $options = $this->getOptions();

        // This process can be expensive and affect perf if enabled. So we have the flexability to skip it.
        if ($options->routingEnabled) {
            $this->listeners[] = $events->attach('loadModule', array($this, 'routesTrigger'), 3000);
        }
        $this->listeners[] = $events->attach('loadModule', array($this, 'initServicesTrigger'), 4000);

        return $this;

    }

    /**
     * Event callback for 'routesTrigger'
     *
     * @param  \Zend\ModuleManager\ModuleEvent $e
     * @return DefaultListenerAggregate
     */
    public function routesTrigger(ModuleEvent $e)
    {
        $module = $e->getParam('module');
        if (is_callable(array($module, 'getRoutes'))) {
            $this->_routes[$e->getParam('moduleName')] = $module->getRoutes();
        }

        return $this;

    }

    /**
     * Event callback for 'initServicesTrigger'
     *
     * @param  \Zend\ModuleManager\ModuleEvent $e
     * @return DefaultListenerAggregate
     */
    public function initServicesTrigger(ModuleEvent $e)
    {
        $module = $e->getParam('module');
        if (is_callable(array($module, 'initServices'))) {
            $this->_services = array_merge($this->_services, $module->initServices($this->_services));
        }

        return $this;

    }

    /**
     * Get the registered routes
     *
     * @return array
     */
    public function getRoutes()
    {
        return $this->_routes;
    }

    /**
     * Get the registered services
     *
     * @return mixed
     */
    public function getServices()
    {
        return $this->_services;
    }

}
