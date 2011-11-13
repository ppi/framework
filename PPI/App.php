<?php
/**
 * This is the PPI Appliations Configuration class which is used in the Bootstrap
 *
 * @package   Core
 * @author    Paul Dragoonis <dragoonis@php.net>
 * @license   http://opensource.org/licenses/mit-license.php MIT
 * @link      http://www.ppiframework.com
 */
namespace PPI;
use PPI\Core\CoreException;
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
		'request'           => null
	);


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
	 * Set the router object for the app bootup
	 *
	 * @param PPI_Router_Interface $router The router object
	 * @return void
	 */
	function setRouter(Router\RouterInterface $router) {
		$this->_envOptions['router'] = $router;
	}

	/**
	 * Set the dispatch object for the app bootup
	 *
	 * @param PPI_Dispatch_Interface $dispatch The dispatch object
	 * @return void
	 */
	function setDispatcher(\PPI\Dispatch\DispatchInterface $dispatch) {
		$this->_envOptions['dispatcher'] = $dispatch;
	}

		/**
	 * Set the request object for the app bootup
	 *
	 * @param object $request
	 * @return void
	 */
	function setRequest($request) {
		$this->_envOptions['request'] = $request;
	}

	/**
	 * Set the session object for the app bootup
	 *
	 * @param PPI_Session_Interface $session The session object
	 * @return void
	 */
	function setSession(PPI_Session_Interface $session) {
		$this->_envOptions['session'] = $session;
	}

	/**
	 * Run the boot process, boot up our app. Call the relevant classes such as:
	 * config, registry, session, dispatch, router.
	 *
	 * @return $this Fluent interface
	 */
	function boot() {

		error_reporting($this->_envOptions['errorLevel']);
		ini_set('display_errors', $this->getEnv('showErrors', 'On'));
		
		// Set the Exception handler
		if($this->_envOptions['exceptionHandler'] === null){
			$exceptionHandler = new \PPI\Exception\Handler();
			// Add Log listener
			$exceptionHandler->addListener(new \PPI\Exception\Log());
			$this->_envOptions['exceptionHandler'] = array($exceptionHandler, 'handle');
		}
		set_exception_handler($this->_envOptions['exceptionHandler']);
		
		// Fire up the default config handler
		if($this->_envOptions['config'] === null) {

			$this->_config = new Config(array(
				'configBlock'     => $this->_envOptions['configBlock'],
				'configFile'      => $this->_envOptions['configFile'],
				'cacheConfig'     => $this->_envOptions['cacheConfig'],
				'configCachePath' => $this->_envOptions['configCachePath']
			));
		}
		
		// Apply the config
		$this->_config = $this->_config->getConfig();

		// So are we auto-loading datasource
		if(isset($this->_envOptions['ds']) && $this->_envOptions['ds']) {
			$ds = DataSource::create($this->loadDSConnections());
			Registry::set('DataSource', $ds);
		}

		// Set the config into the registry for quick read/write
		Registry::set('PPI_Config', $this->_config);

		// Initialise the session
		if(!headers_sent()) {

			// Fire up the default session handler
			if($this->_envOptions['session'] === null) {
				$this->_envOptions['session'] = new Session();
			}
			Registry::set('PPI_Session', $this->_envOptions['session']);
		}

		// By default we always load up PPI_Router as it contains default routes now such as __404__
		if($this->_envOptions['router'] === null) {
			$this->_envOptions['router'] = new Router();
		}

		// -- Fire up the default dispatcher --
		if($this->_envOptions['dispatcher'] === null) {
			$this->_envOptions['dispatcher'] = new Dispatch();
		}

		// -- Set the PPI_Request object --
		if($this->_envOptions['request'] === null) {
			$this->_envOptions['request'] = new Request();
		}

		// -------------- Library Autoloading Process --------------
		if(!empty($this->_config->system->autoloadLibs)) {
			foreach(explode(',', $this->_config->system->autoloadLibs) as $sLib) {
				switch(strtolower(trim($sLib))) {
					case 'zf':
						Autoload::add('Zend', array(
							'path'   => VENDORPATH . 'Zend/'
						));
						break;

					case 'github':
						$githubAutoloader = VENDORPATH . 'Github/Autoloader.php';
						if(!file_exists($githubAutoloader)) {
							throw new CoreException('Unable to autoload github, the github autoloader was no found.');
						}
						include_once($githubAutoloader);
						\Github_Autoloader::register();
						break;

					case 'swift':
						include_once(VENDORPATH . 'Swift/swift_required.php');
						break;

					case 'solar':
						include_once(VENDORPATH . 'Solar.php');
						Autoload::add('Solar', array(
							'path'   => VENDORPATH . 'Solar/',
							'prefix' => 'Solar_'
						));
						break;

				}

			}
		}

		Registry::set('PPI_App', $this);

		return $this; // Fluent Interface
	}

	/**
	 * Call the dispatch process. Running the dispatcher and dispatching
	 *
	 * @return $this Fluent interface
	 */
	function dispatch() {

		// Urls and Uris
		$baseUrl = $this->getConfig()->system->base_url;
		$url = $this->_envOptions['request']->getUrl();

		// Router
		$router = $this->_envOptions['router'];
		$router->setUri(strpos($url, $baseUrl) !== false ? str_replace($baseUrl, '', $url) : $url);
		$router->init();
		$uri = $router->getMatchedRoute();
		
		
		// If we've no URI, dispatch the default route.
		if($uri === '') {
			$this->_envOptions['dispatcher']->setUri($router->getDefaultRoute());
		} else {
			$this->_envOptions['dispatcher']->setUri($uri);
		}
		
		// Lets do our controller check, if it's bogus then attempt the 404 route.
		if(!$this->_envOptions['dispatcher']->check()) {
			$this->_envOptions['dispatcher']->setUri($router->get404Route());
			// If the 404 route we set doesn't exist, lets bomb out we can't go any further.
			if(!$this->_envOptions['dispatcher']->check()) {
				throw new CoreException('Unable to apply the 404 route of: ' . $router->get404Route() . '. Route does not exist');
			}
		}

		// Get the instantiated controller class
		$controller = $this->_envOptions['dispatcher']->getController();

		// Update the URI for the request object so things like $this->get() work
		$this->_envOptions['request'] = new Request(array(
			'uri' => $uri
		));

		Registry::set('PPI_Request', $this->_envOptions['request']);
		Registry::set('PPI_Dispatch', $this->_envOptions['dispatcher']);

		$this->_envOptions['view']     = new View();
		$this->_envOptions['response'] = new Response();

		if($controller !== null) {
			$controller->systemInit($this);
			$this->_envOptions['dispatcher']->setController($controller);
		}
		
		$this->_envOptions['dispatcher']->dispatch();
		
	}
	
	/**
	 * Load the connections from $path
	 * 
	 * @return array
	 */
	function loadDSConnections() {

		$path = $this->getEnv('dsConnectionsPath', CONFIGPATH . 'connections.php');
		include_once($path);
		return isset($connections) ? $connections : array();
	}

	/**
	 * Get the current site mode set, such as 'development' or 'production'
	 *
	 * @return string
	 */
	function getSiteMode() {
		return $this->_envOptions['siteMode'];
	}

	/**
	 * Get the config
	 *
	 * @return void
	 */
	function getConfig() {
		return $this->_config;
	}

}