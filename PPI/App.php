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
	Zend\Module\Manager as ModuleManager,
	PPI\Module\Listener\DefaultListenerAggregate as PPIDefaultListenerAggregate,
	Symfony\Component\HttpFoundation\Request as HttpRequest,
	Symfony\Component\HttpFoundation\Response as HttpResponse,
	Symfony\Component\Routing\RequestContext,
	Symfony\Component\Routing\Matcher\UrlMatcher,
	Symfony\Component\Routing\Exception\ResourceNotFoundException,
	Zend\Module\Listener\ListenerOptions;

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
	 * The constructor.
	 * 
	 * @param array $options
	 */

	function __construct(array $options = array()) {
		if(!empty($options)) {
			foreach ($options as $key => $value) {
				if (method_exists($this, ($sMethod = 'set' . ucfirst($key)))) {
					$this->$sMethod($value);
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

	/**
	 * Run the boot process, boot up our app. Call the relevant classes such as:
	 * config, registry, session, dispatch, router.
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
		$this->_moduleManager = $moduleManager;
		$allRoutes = $defaultListeners->getRoutes();
		
		// Routing preparation
		$matchedRoute    = false;
		$requestContext  = new RequestContext();
		$requestContext->fromRequest($this->_request);

		// Check the routes from our modules
		foreach($allRoutes as $moduleName => $moduleRoutes) {
			try {
				
				$matcher = new UrlMatcher($moduleRoutes, $requestContext);
				$matchedRoute = $matcher->match($this->_request->getPathInfo());
				
				$this->_matchedModule = $this->_moduleManager->getModule($moduleName);
				$this->_matchedModule->setModuleName($moduleName);
				
			} catch(ResourceNotFoundException $e) {} catch(\Exception $e) {}
		}
		
		// Handle 404 here gracefully using $response object
		if($matchedRoute === false) {
			die('404');
		}
		
		// Set our valid route
		$this->_matchedRoute = $matchedRoute;
		
		// Fluent Interface
		return $this;
	}
	
	/**
	 * Lets dispatch our module's controller action
	 */
	function dispatch() {
		
		list($module, $controllerName, $actionName) = explode(':', $this->_matchedRoute['_controller'], 3);
		$actionName = $actionName . 'Action';
		
		$this->_matchedModule->setControllerName($controllerName);
		$this->_matchedModule->setActionName($actionName);

		$className   = "\\{$this->_matchedModule->getModuleName()}\\Controller\\$controllerName";
		$controller  = new $className();
		$controller->setRequest($this->_request)->setResponse($this->_response);

		$result      = $this->_matchedModule->setController($controller)->dispatch();
		$controller  = $this->_matchedModule->getController();
		
		$this->_request   = $controller->getRequest();
		$this->_response  = $controller->getResponse();
		$this->_response->setContent($result);
		$this->_response->send();
		
	}

}