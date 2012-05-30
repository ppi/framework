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
use
	
	// Modules
	PPI\Module\ServiceLocator,
	PPI\Module\Listener\ListenerOptions,
	Zend\ModuleManager\ModuleManager,
	PPI\Module\Listener\DefaultListenerAggregate as PPIDefaultListenerAggregate,

	// Templating
	PPI\Templating\FileLocator,
	PPI\Templating\Twig\TwigEngine,
	PPI\Templating\TemplateLocator,
	PPI\Templating\TemplateNameParser,
	PPI\Templating\Smarty\SmartyEngine,
	PPI\Templating\Php\FileSystemLoader,
	Symfony\Component\Templating\PhpEngine,
	PPI\Templating\Twig\Loader\FileSystemLoader as TwigFileSystemLoader,
	
	// HTTP Stuff and routing
	PPI\Module\Routing\Router,
	PPI\Module\Routing\Loader\YamlFileLoader,
	Symfony\Component\Routing\RequestContext,
	Symfony\Component\Routing\RouteCollection,
	Symfony\Component\Routing\Generator\UrlGenerator,
	Symfony\Component\HttpFoundation\Request as HttpRequest,
	Symfony\Component\HttpFoundation\Response as HttpResponse,
	Symfony\Component\Routing\Exception\ResourceNotFoundException;

class App {
	
	/**
	 * Options for the app
	 * 
	 * @var array
	 */
	protected $_options = array(
		'templatingEngine'    => 'php',
		'useDataSource'       => false,
		'sessionclass'        => 'Symfony\Component\HttpFoundation\Session\Session',
		'sessionstorageclass' => 'Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage'
	);
	
	/**
	 * The session object
	 * 
	 * @var null
	 */
	public $session = null;
	
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
	 * The constructor.
	 * 
	 * @param array $options
	 */

	public function __construct(array $options = array()) {
		
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
	 * @param array $options The options
	 * @return void
	 */
	public function setEnv(array $options) {

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
	public function boot() {

		if(empty($this->_options['moduleConfig']['listenerOptions'])) {
			throw new \Exception('Missing moduleConfig: listenerOptions');
		}
		
		$this->_request  = HttpRequest::createFromGlobals();
		$this->_response = new HttpResponse();
		
		$routerOptions = array();
		if($this->getAppConfigValue('cache_dir') !== null) {
			$routerOptions['cache_dir'] = $this->getAppConfigValue('cache_dir');
		}

		// Initialise the routing components
		$routingEnabled  = true;
		$matchedRoute    = false;
		$routeCollection = new RouteCollection();
		$requestContext  = new RequestContext();
		$requestContext->fromRequest($this->_request);
		
		$router = new Router($requestContext, $routeCollection, $routerOptions);

		if(!$this->isDevMode()) {
			
			// If we are in production mode, and have the routing file(s) have been cached, then skip route fetching on modules boot
			if($router->isGeneratorCached() && $router->isMatcherCached()) {
				$this->_options['moduleConfig']['listenerOptions']['routingEnabled'] = false;
				$routingEnabled = false;
			}

		}
		
		// Module Listeners
		$listenerOptions  = new ListenerOptions($this->_options['moduleConfig']['listenerOptions']);
		$defaultListener = new PPIDefaultListenerAggregate($listenerOptions);

		// Loading our Modules
		$moduleManager = new ModuleManager($this->_options['moduleConfig']['activeModules']);
		$moduleManager->events()->attachAggregate($defaultListener);


		$moduleManager->loadModules();
		
		// If the routing process for modules has been cached or not.
		if($routingEnabled) {

			// Merging all the other route collections together from the modules
			$allRoutes = $defaultListener->getRoutes();
			foreach($allRoutes as $routes) {
				$routeCollection->addCollection($routes);
			}
			$router->setRouteCollection($routeCollection);
		}

		try {
			
			// Lets load up our router and match the appropriate route
			$router->warmUp();

			$matchedRoute         = $router->match($this->_request->getPathInfo());
			$matchedModuleName    = $matchedRoute['_module'];
			$this->_matchedModule = $moduleManager->getModule($matchedModuleName);
			$this->_matchedModule->setModuleName($matchedModuleName);

		} catch(ResourceNotFoundException $e) {} catch(\RuntimeException $e) {
			die($e->getMessage());
		} catch(\Exception $e) {}

		// @todo Handle 404 here gracefully using $response object
		if($matchedRoute === false) {
			die('404');
		}

		// Set our valid route
		$this->_matchedRoute  = $matchedRoute;
		$this->_moduleManager = $moduleManager;
		
		$defaultServices = array(
			'request'       => $this->_request,
			'response'      => $this->_response,
			'templating'    => $this->getTemplatingEngine(),
			'session'       => $this->getSession(),
			'router'        => $router,
			'config'        => $defaultListener->getConfigListener()->getMergedConfig(false)
		);
		
		// If the user wants DataSource available in their application, lets insntantiate it and set up their connections
		$dsConnections = $this->getAppConfigValue('datasource.connections');
		if($this->_options['useDataSource'] === true && $dsConnections !== null) {
			$defaultServices['dataSource'] = $this->getDataSource($dsConnections);
		}
		
		// Services
		$this->_serviceLocator = new ServiceLocator(array_merge($defaultServices, $defaultListener->getServices()));
		
		// Fluent Interface
		return $this;
	}
	
	/**
	 * Lets dispatch our module's controller action
	 */
	public function dispatch() {
		
		// Lets disect our route
		list($module, $controllerName, $actionName) = explode(':', $this->_matchedRoute['_controller'], 3);
		$actionName = $actionName . 'Action';

		// Instantiate our chosen controller
		$className  = "\\{$this->_matchedModule->getModuleName()}\\Controller\\$controllerName";
		$controller = new $className();
		
		// Set Dependencies for our controller
		$controller->setServiceLocator($this->_serviceLocator);
		
		// Lets do setter injection on our controller
		$controller->injectServices();
		
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
		
		$fileLocator = new FileLocator(array(
			'modules'     => $this->_moduleManager->getModules(),
			'modulesPath' => realpath($this->_options['moduleConfig']['listenerOptions']['module_paths'][0]),
			'appPath'     => getcwd() . '/app'
		));
		
		$templateLocator = new TemplateLocator($fileLocator);
		
		switch($this->getOption('templatingEngine')) {
			
			case 'twig':
				
				return new TwigEngine(
					new \Twig_Environment(
						new TwigFileSystemLoader(
							$templateLocator,
							new TemplateNameParser()
						)
					),
					new TemplateNameParser(),
					$templateLocator
				);
			
			case 'smarty':
				
				defined('SMARTY_DIR') || define('SMARTY_DIR', PPI_VENDOR_PATH . 'Smarty/');
				$smartyDriver = new \PPI\Templating\Smarty\Smarty();
				$engine = new SmartyEngine(
					$smartyDriver,
					new TemplateNameParser(),
					new FileSystemLoader($templateLocator)
				);
				return $engine;
				
			case 'php':
			default:
			
				return new PhpEngine(
					new TemplateNameParser(), 
					new FileSystemLoader($templateLocator), array(
						new \Symfony\Component\Templating\Helper\SlotsHelper()
					)
				);
			
		}
		

	}
	
	/**
	 * Instantiate the DataSource component, optionally taking in its connections
	 * 
	 * @param array $connections
	 * @return DataSource\DataSource
	 */
	protected function getDataSource(array $connections = array()) {
		return new \PPI\DataSource\DataSource($connections);
	}
	
	/**
	 * Get the session class
	 * 
	 * @return \Symfony\Component\HttpFoundation\Session\Session
	 */
	protected function getSession() {
		if($this->session === null) {
			$session = new $this->_options['sessionclass'](new $this->_options['sessionstorageclass']());
			$session->start();
			$this->session = $session;
		}
		return $session;
	}
	
	/**
	 * Get an option
	 * 
	 * @param $key
	 * @param null $default
	 * @return null
	 */
	public function getOption($key, $default = null) {
		return isset($this->_options[$key]) ? $this->_options[$key] : $default; 
	}
	
	/**
	 * Get a config value from the application config
	 * 
	 * @param string $key
	 * @param mixed $default
	 * @return mixed
	 */
	public function getAppConfigValue($key, $default = null) {
		return isset($this->_options['config'][$key]) ? $this->_options['config'][$key] : $default;
	}
	
	/**
	 * Set the option
	 * 
	 * @param $key
	 * @param $val
	 */
	public function setOption($key, $val) {
		$this->_options[$key] = $val;
	}

	/**
	 * Magic setter function, this is an alias of setOption
	 *
	 * @param string $option The Option
	 * @param string $value The Value
	 * @return void
	 */
	public function __set($option, $value) {
		$this->_options[$option] = $value;
	}
	
	/**
	 * Get the environment mode the application is in.
	 * 
	 * @return string 
	 */
	public function getEnv() {
		return $this->getAppConfigValue('environment');
	}
	
	/**
	 * Check if the application is in development mode.
	 * 
	 * @return bool
	 */
	public function isDevMode() {
		return $this->getEnv() === 'development';
	}
	
	/**
	 * Magic getter function, this is an alias of getEnv()
	 *
	 * @param string $option The Option
	 * @return mixed
	 */
	public function __get($option) {
		return isset($this->_options[$option]) ? $this->_options[$option] : null;
	}

}