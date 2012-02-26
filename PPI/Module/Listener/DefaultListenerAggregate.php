<?php
namespace PPI\Module\Listener;
use Zend\Module\Listener\DefaultListenerAggregate as ZendDefaultListenerAggregate,
	Zend\EventManager\EventCollection,
	Zend\Module\ModuleEvent;

class DefaultListenerAggregate extends ZendDefaultListenerAggregate {
	
	/**
	 * The routes registered for our 
	 * 
	 * @var array
	 */
	protected $_routes = array();
	
	public function attach(EventCollection $events) {
		parent::attach($events);
		$options = $this->getOptions();
		$this->listeners[] = $events->attach('loadModule', array($this, 'routesTrigger'), 3000);
		return $this;
	}
	
	public function routesTrigger(ModuleEvent $e) {
		$module = $e->getParam('module');
		if (is_callable(array($module, 'getRoutes'))) {
			$this->_routes += $module->getRoutes();
		}
		return $this;
	}
	
	public function getRoutes() {
		return $this->_routes;
	}
	
}