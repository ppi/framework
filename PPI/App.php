<?php
/**
 * The PPI App bootstrap class. 
 * This class sets various app settings, and allows you to override clases used in the bootup process.
 *
 * @package   Core
 * @author    Paul Dragoonis <dragoonis@php.net>
 * @license   http://opensource.org/licenses/mit-license.php MIT
 * @link      http://www.ppi.io
 */
namespace PPI;
use PPI\Core\CoreException,
	
	// Modules
	Zend\Module\Manager as ModuleManager,
	Zend\Module\Listener\ListenerOptions,
	PPI\Module\Listener\DefaultListenerAggregate as PPIDefaultListenerAggregate,
	PPI\Module\ServiceLocator,
	
	// Templating
	PPI\Module\Templating\FileSystemLoader,
	PPI\Module\Templating\TemplateLocator,
	PPI\Module\Templating\FileLocator,
	PPI\Module\Templating\TemplateNameParser,
	
	// HTTP Stuff and routing
	Symfony\Component\HttpFoundation\Request as HttpRequest,
	Symfony\Component\HttpFoundation\Response as HttpResponse,
	Symfony\Component\Routing\RequestContext,
	Symfony\Component\Routing\Matcher\UrlMatcher,
	Symfony\Component\Routing\RouteCollection,
	Symfony\Component\Routing\Generator\UrlGenerator,
	Symfony\Component\HttpFoundation\Session\Session,
	Symfony\Component\HttpFoundation\Session\Storage\NativeFileSessionStorage,
	Symfony\Component\Routing\Exception\ResourceNotFoundException;

class App {

	/**
	 * The Environment Options for the PPI Application
	 *
	 * @var array
	 */
	protected $_envOptions = array(
		'siteMode'          => 'development', // This determines how PPI handles things like exceptions
		'configBlock'       => 'development', // The block in the config file to get the config data from
		'configFile'        => 'general.ini', // The default filename for the config file
		'configCachePath'   => '', // The path to the config cache
		'cacheConfig'       => false, // Config object caching
		'errorLevel'        => E_ALL, // The error level to throw via error_reporting()
		'showErrors'        => 'On', // Whether to display errors or not. This gets fired into ini_set('display_errors')
		'exceptionHandler'  => null, // Callback accepted by set_exception_handler()
		'router'            => null,
		'session'           => null,
		'config'            => null,
		'dispatcher'        => null,
		'request'           => null,
		'modules'           => array()
	);
	
	/**
	 * @var null|array
	 * 
	 */
	protected $_matchedRoute = null;
	
	/**
	 * The module manager
	 * 
	 * @var null
	 */
	protected $_moduleManager = null;
	
	/**
	 * The request object
	 * 
	 * @var null
	 */
	protected $_request = null;
	
	/**
	 * The response object
	 * 
	 * @var null
	 */
	protected $_response = null;
	
	/**
	 * The matched module from the matched route.
	 * 
	 * @var null
	 */
	protected $_matchedModule = null;
	
	/**
	 * Service Locator
	 * 
	 * @var null|\PPI\Module\ServiceLocator
	 */
	protected $_serviceLocator = null;

	/**
	 * Options for the app
	 * 
	 * @var array
	 */
	protected $_options = array();

	/**
	 * The constructor.
	 * 
	 * @param array $options
	 */

	function __construct(array $options = array()) {
		
		if(!empty($options)) {
			foreach ($options as $key => $value) {
				$prop = '_' . $key;
				if (property_exists($this, $prop)) {
					$this->$prop = $value;
				}
			}
		}
	}

	/**
	 * Setter for the environment, passing in options determining how the app will behave
	 *
	 * @param array $p_aOptions The options
	 * @return void
	 */
	function setEnv(array $options) {

		// If we pass in a bad sitemode, lets just default to 'development' gracefully.
		if(isset($options['siteMode'])) {
			if(!in_array($options['siteMode'], array('development', 'production'))) {
				unset($options['siteMode']);
			}
		}

		// Any further options passed, eg: it maps; 'errorLevel' to $this->_errorLevel
		foreach($options as $optionName => $option) {
			$this->_envOptions[$optionName] = $option;
		}
	}

	/**
	 * Run the boot process, boot up our modules and their dependencies. 
	 * Decide on a route for $this->dispatch() to use.
	 *
	 * @return $this Fluent interface
	 */
	function boot() {

		if(empty($this->_envOptions['moduleConfig']['listenerOptions'])) {
			throw new \Exception('Missing moduleConfig: listenerOptions');
		}
		
		// Core Objects
		$this->_request  = HttpRequest::createFromGlobals();
		$this->_response = new HttpResponse();

		// Module Listeners
		$listenerOptions  = new ListenerOptions($this->_envOptions['moduleConfig']['listenerOptions']);
		$defaultListeners = new PPIDefaultListenerAggregate($listenerOptions);
		
		// Loading our Modules
		$moduleManager = new ModuleManager($this->_envOptions['moduleConfig']['activeModules']);
		$moduleManager->events()->attachAggregate($defaultListeners);
		$moduleManager->loadModules();

		// Routing preparation
		$allRoutes         = $defaultListeners->getRoutes();
		$matchedRoute      = false;
		$requestContext    = new RequestContext();
		$pathInfo          = $this->_request->getPathInfo();
		$globalRoutes      = new RouteCollection();
		$requestContext->fromRequest($this->_request);
		
		// Make a route collection, merging all the other route collections from the modules
		foreach($allRoutes as $routes) {
			$globalRoutes->addCollection($routes);
		}
		
		try {
			$matcher              = new UrlMatcher($globalRoutes, $requestContext);
			$matchedRoute         = $matcher->match($pathInfo);
			$moduleName           = $matchedRoute['_module'];
			$this->_matchedModule = $moduleManager->getModule($moduleName);
			$this->_matchedModule->setModuleName($moduleName);

		} catch(ResourceNotFoundException $e) {} catch(\Exception $e) {}

		// @todo Handle 404 here gracefully using $response object
		if($matchedRoute === false) {
			die('404');
		}

		// Set our valid route
		$this->_matchedRoute = $matchedRoute;
		$this->_moduleManager = $moduleManager;
		
		$defaultServices = array(
			'request'       => $this->_request,
			'response'      => $this->_response,
			'templating'    => $this->getTemplatingEngine(),
			'url.generator' => new UrlGenerator($globalRoutes, $requestContext),
			'session'       => $this->getSession()
		);
		
		// Services
		$this->_serviceLocator = new ServiceLocator(array_merge($defaultServices, $defaultListeners->getServices()));
		
		// Fluent Interface
		return $this;
	}
	
	/**
	 * Lets dispatch our module's controller action
	 */
	function dispatch() {
		
		// Lets disect our route
		list($module, $controllerName, $actionName) = explode(':', $this->_matchedRoute['_controller'], 3);
		$actionName = $actionName . 'Action';

		// Instantiate our chosen controller
		$className  = "\\{$this->_matchedModule->getModuleName()}\\Controller\\$controllerName";
		$controller = new $className();
		
		// Set Dependencies for our controller
		$controller->setServiceLocator($this->_serviceLocator);
		
		// Prep our module for dispatch
		$this->_matchedModule
			->setControllerName($controllerName)
			->setActionName($actionName)
			->setController($controller);

		// Dispatch our action, return the content from the action called.
		$result = $this->_matchedModule->dispatch();

		$serviceLocator = $controller->getServiceLocator();
		
		// The controller manipulates the state of the Request and Response so after dispatch has occurred
		// Then we obtain these back for further uses.
		$controller      = $this->_matchedModule->getController();
		$this->_request  = $serviceLocator->get('request');
		$this->_response = $serviceLocator->get('response');
		
		// Send our content to the browser
		$this->_response->setContent($result);
		$this->_response->send();
		
	}
	
	protected function getTemplatingEngine() {
		return new \Symfony\Component\Templating\PhpEngine(
			new TemplateNameParser(), 
			new FileSystemLoader(
				new TemplateLocator(
					new FileLocator(array(
						'modules'     => $this->_moduleManager->getModules(),
						'modulesPath' => realpath($this->_envOptions['moduleConfig']['listenerOptions']['module_paths'][0]),
						'appPath'     => getcwd() . '/app'
					))
				)
			), array(
				new \Symfony\Component\Templating\Helper\SlotsHelper()
			)
			
		);
	}
	
	/**
	 * Get the session class
	 * 
	 * @return \Symfony\Component\HttpFoundation\Session\Session
	 */
	protected function getSession() {
		$session = new Session(new NativeFileSessionStorage());
		$session->start();
		return $session;
	}
	
	/**
	 * Get an option
	 * 
	 * @param $key
	 * @param null $default
	 * @return null
	 */
	function getOption($key, $default = null) {
		return isset($this->_options[$key]) ? $this->_options[$key] : $default; 
	}
	
	/**
	 * Set the option
	 * 
	 * @param $key
	 * @param $val
	 */
	function setOption($key, $val) {
		$this->_options[$key] = $val;
	}

	/**
	 * Magic setter function, this is an alias of setEnv()
	 *
	 * @param string $option The Option
	 * @param string $value The Value
	 * @return void
	 */
	function __set($option, $value) {
		$this->setEnv(array($option => $value));
	}
	
	/**
	 * Obtain the value of an environment option
	 *
	 * @param string $key The Environment Option
	 * @param mixed $default The default value to return if the key is not found
	 * @return mixed If your key is not found, then NULL is returned
	 */
	function getEnv($key, $default = null) {
		return isset($this->_envOptions[$key]) ? $this->_envOptions[$key] : $default;
	}
	
	/**
	 * Magic getter function, this is an alias of getEnv()
	 *
	 * @param string $option The Option
	 * @return mixed
	 */
	function __get($option) {
		return $this->getEnv($option);
	}

}