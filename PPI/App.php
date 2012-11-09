<?php
/**
 * This file is part of the PPI Framework.
 *
 * @copyright  Copyright (c) 2012 Paul Dragoonis <paul@ppi.io>
 * @license    http://opensource.org/licenses/mit-license.php MIT
 * @link       http://www.ppi.io
 */
namespace PPI;

use

    // Config
    PPI\Config\FileLocator,
    PPI\Config\Loader\DelegatingLoader,
    PPI\Config\Loader\LoaderResolver,
    PPI\Config\Loader\ArrayLoader,
    PPI\Config\Loader\PhpFileLoader,
    PPI\Config\Loader\YamlFileLoader,

    // Exceptions
    PPI\Exception\Handler as ExceptionHandler,

    // Services
    PPI\ServiceManager\ServiceManager,
    PPI\ServiceManager\Config\HttpConfig,
    PPI\ServiceManager\Config\SessionConfig,
    PPI\ServiceManager\Config\ModuleConfig,
    PPI\ServiceManager\Config\RouterConfig,
    PPI\ServiceManager\Config\TemplatingConfig,
    PPI\ServiceManager\Options\AppOptions,

    // HTTP Stuff and routing
    PPI\Module\Routing\RoutingHelper;

/**
 * The PPI App bootstrap class.
 *
 * This class sets various app settings, and allows you to override clases used
 * in the bootup process.
 *
 * @author     Paul Dragoonis <paul@ppi.io>
 * @author     Vítor Brandão <vitor@ppi.io>
 * @package    PPI
 * @subpackage Core
 */
class App implements AppInterface
{
    /**
     * Version string.
     *
     * @var string
     */
    const VERSION = '2.0.0-DEV';

    protected $environment;
    protected $debug;
    protected $config;

    /**
     * Application Options.
     *
     * @var array
     */
    protected $options;

    /**
     * The session object
     *
     * @var null
     */
    public $session = null;

    /**
     * @var null|array
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
     * The router object
     *
     * @var null
     */
    protected $_router = null;

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
     * Path to the application root dir aka the "app" directory.
     *
     * @var null|string
     */
     protected $rootDir = null;

    /**
     * Service Manager (ZF2 implementation)
     *
     * @var \PPI\Module\ServiceManager\ServiceManager
     */
     protected $serviceManager = null;

    /**
     * Constructor
     *
     * @param string  $environment The environment
     * @param Boolean $debug       Whether to enable debugging or not
     * @param mixed   $config      Array with application configuration
     */
    public function __construct($environment = 'production', $debug = false, array $config = array())
    {
        $this->environment = $environment;
        $this->debug = (boolean) $debug;

        $this->rootDir = $this->getRootDir();
        $this->name = $this->getName();

        $this->config = array_merge(array('parameters' => $this->getAppParameters()), $config);
    }

    /**
     * Run the boot process, boot up our modules and their dependencies.
     * Decide on a route for $this->dispatch() to use.
     *
     * @return $this
     */
    public function boot()
    {
        $this->options = new AppOptions(array());
        if (isset($this->options['config'])) {
            $this->options->add($this->options['config']);
        }

        // Lets setup exception handlers to catch anything that fails during boot as well.
        $exceptionHandler = new ExceptionHandler();
        $exceptionHandler->addHandler(new \PPI\Exception\Log());
        set_exception_handler(array($exceptionHandler, 'handle'));

        if ($this->getEnv() !== 'production') {
            set_error_handler(array($exceptionHandler, 'handleError'));
        }

        if (!$this->options->has('moduleconfig') || empty($this->options['moduleconfig']['listenerOptions'])) {
            throw new \Exception('Missing moduleConfig: listenerOptions');
        }

        // all user and app configuration must be set up to this point
        $this->serviceManager = new ServiceManager($this->options, array(
            new HttpConfig(),
            new SessionConfig(),
            new ModuleConfig(),
            new RouterConfig(),
            new TemplatingConfig()
        ));

        // resolve options placeholders
        $this->serviceManager->compile();

        $this->_request  = $this->serviceManager->get('request');
        $this->_response = $this->serviceManager->get('response');

        // Loading our Modules
        $defaultListener = $this->serviceManager->get('module.defaultListener');
        $this->_moduleManager = $this->serviceManager->get('module.manager');
        $this->_moduleManager->loadModules();

        // CONFIG - Merge the app config with the config from all the modules
        $this->serviceManager->set('config', $defaultListener->getConfigListener()->getMergedConfig(false));

        // SERVICES - Lets get all the services our of our modules and start setting them in the ServiceManager
        $moduleServices = $defaultListener->getServices();

        foreach ($moduleServices as $serviceKey => $serviceVal) {
            $this->serviceManager->setFactory($serviceKey, $serviceVal);
        }

        // ROUTING
        $this->_router = $this->serviceManager->get('router');
        $this->handleRouting();

        // DATASOURCE - If the user wants DataSource available in their application, lets instantiate it and set up their connections
        $dsConnections = $this->options->get('datasource.connections');

        if ($this->options['useDataSource'] === true && $dsConnections !== null) {
             $this->serviceManager->set('datasource', new \PPI\DataSource\DataSource($dsConnections));
        }

        // Fluent Interface
        return $this;
    }

    /**
     * Lets dispatch our module's controller action
     *
     * @return void
     */
    public function dispatch()
    {
        // Lets dissect our route
        list($module, $controllerName, $actionName) = explode(':', $this->_matchedRoute['_controller'], 3);
        $actionName = $actionName . 'Action';

        // Instantiate our chosen controller
        $className  = "\\{$this->_matchedModule->getModuleName()}\\Controller\\$controllerName";
        $controller = new $className();

        // Set Services for our controller
        $controller->setServiceLocator($this->serviceManager);

        // Set the options for our controller
        $controller->setOptions(array(
            'environment' => $this->getEnv()
        ));

        // Lets create the routing helper for the controller, we unset() reserved keys & what's left are route params
        $routeParams = $this->_matchedRoute;
        $activeRoute = $routeParams['_route'];
        unset($routeParams['_module'], $routeParams['_controller'], $routeParams['_route']);

        // Pass in the routing params, set the active route key
        $routingHelper = $this->serviceManager->get('routing.helper');
        $routingHelper->setParams($routeParams);
        $routingHelper->setActiveRouteName($activeRoute);

        // Register our routing helper into the controller
        $controller->setHelper('routing', $routingHelper);

        // Prep our module for dispatch
        $this->_matchedModule
            ->setControllerName($controllerName)
            ->setActionName($actionName)
            ->setController($controller);

        // Dispatch our action, return the content from the action called.
        $controller = $this->_matchedModule->getController();
        $this->serviceManager = $controller->getServiceLocator();
        $result = $this->_matchedModule->dispatch();

        switch (true) {

            // If the controller is just returning HTML content then that becomes our body response.
            case is_string($result):
                $response = $controller->getServiceLocator()->get('response');
                break;

            // The controller action didn't bother returning a value, just grab the response object from SM
            case is_null($result):
                $response = $controller->getServiceLocator()->get('response');
                break;

            // Anything else is unpredictable so we safely rely on the SM
            default:
                $response = $result;
                break;

        }

        $this->_response = $response;
        $this->_response->setContent($result);

        if ($this->getOption('app.auto_dispatch')) {
            $this->_response->send();
        }
    }

    /**
     * Match a route based on the specified $uri.
     * Set up _matchedRoute and _matchedModule too
     *
     * @param string $uri
     *
     * @return void
     */
    protected function matchRoute($uri)
    {
        $this->_matchedRoute  = $this->_router->match($uri);
        $matchedModuleName    = $this->_matchedRoute['_module'];
        $this->_matchedModule = $this->_moduleManager->getModule($matchedModuleName);
        $this->_matchedModule->setModuleName($matchedModuleName);
    }

    /**
     * @todo Add inline documentation.
     *
     * @return void
     */
    protected function handleRouting()
    {
        try {

            // Lets load up our router and match the appropriate route
            $this->_router->warmUp();
            $this->matchRoute($this->_request->getPathInfo());

        } catch (\Exception $e) {
            $this->_matchedRoute = false;
        }

        // Lets grab the 'Framework 404' route and dispatch it.
        if ($this->_matchedRoute === false) {

            try {

                $baseUrl  = $this->_router->getContext()->getBaseUrl();
                $routeUri = $this->_router->generate($this->options['404RouteName']);

                // We need to strip /myapp/public/404 down to /404, so our matchRoute() to work.
                if (!empty($baseUrl) && ($pos = strpos($routeUri, $baseUrl)) !== false ) {
                    $routeUri = substr_replace($routeUri, '', $pos, strlen($baseUrl));
                }

                $this->matchRoute($routeUri);

            // @todo handle a 502 here
            } catch (\Exception $e) {
                throw new \Exception('Unable to load 404 page. An internal error occured');
            }
        }
    }

    /**
     * Gets the name of the application.
     *
     * @return string The application name
     *
     * @api
     */
    public function getName()
    {
        if (null === $this->name) {
            $this->name = preg_replace('/[^a-zA-Z0-9_]+/', '', basename($this->rootDir));
        }

        return $this->name;
    }

    /**
     * Setter for the environment, passing in options determining how the app will behave
     *
     * @param array $options The options
     *
     * @return void
     */
    public function setEnv(array $options)
    {
        // If we pass in a bad sitemode, lets just default to 'development' gracefully.
        if (isset($options['siteMode'])) {
            if (!in_array($options['siteMode'], array('development', 'production'))) {
                unset($options['siteMode']);
            }
        }

        // Any further options passed, eg: it maps; 'errorLevel' to $this->_errorLevel
        foreach ($options as $optionName => $option) {
            $this->_envOptions[$optionName] = $option;
        }
    }

    /**
     * Get the environment mode the application is in.
     *
     * @return string The current environment
     *
     * @api
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * Get the environment mode the application is in.
     *
     * @return string The current environment
     */
    public function getEnv()
    {
        return $this->getEnvironment();
    }

    /**
     * Check if the application is in development mode.
     *
     * @return boolean
     */
    public function isDevMode()
    {
        return $this->getEnvironment() === 'development';
    }

    /**
     * Checks if debug mode is enabled.
     *
     * @return boolean true if debug mode is enabled, false otherwise
     *
     * @api
     */
    public function isDebug()
    {
        return $this-debug;
    }

    /**
     * Gets the application root dir.
     *
     * @return string The application root dir
     *
     * @api
     */
    public function getRootDir()
    {
        if (null === $this->rootDir) {
            $this->rootDir = realpath(getcwd() . '/app');
        }

        return $this->rootDir;
    }

    /**
     * Get the service manager
     *
     * @return ServiceManager\ServiceManager
     */
    public function getServiceManager()
    {
        return $this->serviceManager;
    }

    /**
     * Gets the cache directory.
     *
     * @return string The cache directory
     *
     * @api
     */
    public function getCacheDir()
    {
        return $this->rootDir.'/cache/'.$this->environment;
    }

    /**
     * Gets the log directory.
     *
     * @return string The log directory
     *
     * @api
     */
    public function getLogDir()
    {
        return $this->rootDir.'/logs';
    }

    /**
     * Gets the charset of the application.
     *
     * @return string The charset
     *
     * @api
     */
    public function getCharset()
    {
        return 'UTF-8';
    }

    /**
     * Get an option
     *
     * @param string $key
     * @param null   $default
     *
     * @return null
     */
    public function getOption($key, $default = null)
    {
        return $this->options->has($key) ? $this->options->get($key) : $default;
    }

    /**
     * Set the option
     *
     * @param $key
     * @param $val
     *
     * @return void
     */
    public function setOption($key, $val)
    {
        $this->options[$key] = $val;
    }

    /**
     * Magic setter function, this is an alias of setOption
     *
     * @param string $option The Option
     * @param string $value  The Value
     *
     * @return void
     */
    public function __set($option, $value)
    {
        $this->options[$option] = $value;
    }

    /**
     * Magic getter function, this is an alias of getEnv()
     *
     * @param string $option The Option
     *
     * @return mixed
     */
    public function __get($option)
    {
        return isset($this->options[$option]) ? $this->options[$option] : null;
    }

    /**
     * Returns the application parameters.
     *
     * @return array An array of application parameters
     */
    protected function getAppParameters()
    {
        return array_merge(
            array(
                'app.root_dir'        => $this->rootDir,
                'app.environment'     => $this->environment,
                'app.debug'           => $this->debug,
                'app.name'            => $this->name,
                'app.cache_dir'       => $this->getCacheDir(),
                'app.logs_dir'        => $this->getLogDir(),
                'app.charset'         => $this->getCharset(),
            ),
            $this->getEnvParameters()
        );
    }

    /**
     * Gets the environment parameters.
     *
     * Only the parameters starting with "PPI__" are considered.
     *
     * @return array An array of parameters
     */
    protected function getEnvParameters()
    {
        $parameters = array();
        foreach ($_SERVER as $key => $value) {
            if (0 === strpos($key, 'PPI__')) {
                $parameters[strtolower(str_replace('__', '.', substr($key, 5)))] = $value;
            }
        }

        return $parameters;
    }

    /**
     * Loads a configuration file or PHP array.
     *
     * @param mixed  $resource The resource
     * @param string $type     The resource type
     */
    public function loadConfig($resource, $type = null)
    {
        $loader = $this->getConfigLoader();
        $config = $loader->load($resource, $type);

        $this->options->add($config);

        return $this;
    }

    /**
     * Returns a loader for the App configuration.
     *
     * @return DelegatingLoader The loader
     */
    protected function getConfigLoader()
    {
        if (null === $this->loader) {
            $locator = new FileLocator($this->getRootDir());
            $resolver = new LoaderResolver(array(
                new YamlFileLoader($locator),
                new PhpFileLoader($locator),
                new ArrayLoader(),
            ));

            $this->loader = new DelegatingLoader($resolver);
        }

        return $this->loader;
    }
}
