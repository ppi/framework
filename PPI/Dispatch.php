<?php
/**
 * The Dispatch Class For The PPI Framework.
 *
 * @author    Paul Dragoonis <dragoonis@php.net>
 * @license   http://opensource.org/licenses/mit-license.php MIT
 * @package   Dispatch
 * @link      www.ppiframework.com
 */
namespace PPI;
class Dispatch {


	/**
	 * The uri to dispatch against
	 *
	 * @var null|string
	 */
	protected $_uri = null;

	/**
	 * The string name of the controller
	 *
	 * @var null|string
	 */
	protected $_controllerName = null;

	/**
	 * The Controller's Class Name
	 *
	 * @var null|string
	 */
	protected $_controllerClassName = null;

	/**
	 * The chosen method name for our controller, defaulted to index
	 *
	 * @var string
	 */
	protected $_methodName = 'index';

	/**
	 * The controller class that has been instantiated
	 *
	 * @var null|object
	 */
	protected $_controllerObject = null;

	/**
	 * Identify and store the appropriate Controller and Methods to dispatch at a later time when calling dispatch()
	 *
	 */
	function __construct(array $options = array()) {

		if(isset($options['uri'])) {
			$this->_uri = $options['uri'];
		}

	}

	/**
	 * Set the URI
	 *
	 * @param string $uri
	 * @return void
	 */
	function setUri($uri) {
		$this->_uri = $uri;
	}

	/**
	 * Get the URI
	 *
	 * @return string
	 */
	function getUri() {
		return $this->_uri;
	}

	function init() {

		$urls                = $this->getURISegments();
		$controllerName      = ucfirst($urls[0]);
		$className           = 'App\\Controller\\' . $controllerName; // eg: App\Controller\User

		$this->setControllerName(strtolower($controllerName));
		$this->setControllerClassName($className);

		// Setup the method we wish to call.
		$method = isset($urls[1]) ? $urls[1] : $this->_methodName;
		$this->setMethodName($method);
	}

	/**
	 * Check if the chosen controller is a valid controller
	 *
	 * @return bool
	 */
	function check() {

		// Init check
		$this->init();

		// Lets check if our controller exists
		$className = $this->getControllerClassName();
		
		$exists = class_exists($className);
		if(!$exists) {
			return false;
		}

		// Check for an abstract class
		$reflectionClass = new \ReflectionClass($className);
		if($reflectionClass->isAbstract() === true) {
			return false;
		}

		$controller = new $className();

		// Check that it's callable
		if(!is_callable(array($controller, $this->getMethodName()))) {
			return false;
		}

		// Set the instantiated controller
		$this->setController($controller);
		return true;
	}

	/**
	 * Call the dispatch process for the current  set helper
	 *
	 * @return void
	 */
	function dispatch() {

		$controller = $this->getController();
		$method     = $this->getMethodName();

		if(method_exists($controller, 'preDispatch')) {
			$controller->preDispatch();
		}

		$controller->$method();

		if(method_exists($controller, 'postDispatch')) {
			$controller->postDispatch();
		}
	}

	/**
	 * Get the segments from the URI
	 *
	 * @return array
	 */
	function getURISegments() {

		$uri = $this->_uri;
		return explode('/', trim($uri, '/'));
	}

	/**
	 * Get the currently chosen controller name
	 *
	 * @return string
	 */
	function getControllerName() {
		return $this->_controllerName;
	}

	/**
	 * Get the controller's class name
	 *
	 * @return null|string
	 */
	function getControllerClassName() {
		return $this->_controllerClassName;
	}

	/**
	 * Set the controller name
	 *
	 * @param string $name The Controller Name
	 * @return void
	 */
	function setControllerName($name) {
		$this->_controllerName = $name;
	}

	/**
	 * Set the controller's Class NAme
	 *
	 * @param string $name
	 * @return void
	 */
	function setControllerClassName($name) {
		$this->_controllerClassName = $name;
	}

	/**
	 * Get the current set method name on the chosen class.
	 *
	 * @return string
	 */
	function getMethodName() {
		return $this->_methodName;
	}

	/**
	 * Set the method name
	 *
	 * @param string $name
	 * @return void
	 */
	function setMethodName($name) {
		$this->_methodName = $name;
	}

	/**
	 * Set the controller object
	 *
	 * @param object $controller
	 * @return void
	 */
	function setController($controller) {
		$this->_controllerObject = $controller;
	}

	/**
	 * Get the controller object
	 *
	 * @return null|object
	 */
	function getController() {
		return $this->_controllerObject;
	}

}
