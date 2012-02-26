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
	Zend\Module\Manager as ModuleManager;

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

//		error_reporting($this->_envOptions['errorLevel']);
//		ini_set('display_errors', $this->getEnv('showErrors', 'On'));
		
		// Set the Exception handler
//		if($this->_envOptions['exceptionHandler'] === null){
//			$exceptionHandler = new \PPI\Exception\Handler();
			// Add Log Handler
//			$exceptionHandler->addHandler(new \PPI\Exception\Log());
//			$this->_envOptions['exceptionHandler'] = array($exceptionHandler, 'handle');
//		}
//		set_exception_handler($this->_envOptions['exceptionHandler']);
		
		if(!empty($this->_envOptions['moduleConfig']['listenerOptions'])) {
			
			$listenerOptions  = new \Zend\Module\Listener\ListenerOptions($this->_envOptions['moduleConfig']['listenerOptions']);
			$defaultListeners = new \Zend\Module\Listener\DefaultListenerAggregate($listenerOptions);
			
			$moduleManager = new ModuleManager($this->_envOptions['moduleConfig']['activeModules']);
			$moduleManager->events()->attachAggregate($defaultListeners);
			$moduleManager->loadModules();
		}
		
		return $this; // Fluent Interface
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