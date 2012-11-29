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
class App
{
    /**
     * Version string.
     *
     * @var string
     */
    const VERSION = '2.0.0-DEV';

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
     * Service Manager (ZF2 implementation)
     *
     * @var \PPI\Module\ServiceManager\ServiceManager
     */
     protected $serviceManager;

    /**
     * The constructor.
     *
     * @param array $options
     *
     * @return void
     */
    public function __construct(array $options = array())
    {
        $this->options = new AppOptions($options);
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
     * Run the boot process, boot up our modules and their dependencies.
     * Decide on a route for $this->dispatch() to use.
     *
     * @return $this
     */
    public function boot()
    {
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
     * Get the environment mode the application is in.
     *
     * @return string
     */
    public function getEnv()
    {
        return $this->options->get('environment');
    }

    /**
     * Check if the application is in development mode.
     *
     * @return boolean
     */
    public function isDevMode()
    {
        return $this->getEnv() === 'development';
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
        return $this->options->get('debug');
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
     * Get the service manager
     *
     * @return ServiceManager\ServiceManager
     */
    public function getServiceManager()
    {
        return $this->serviceManager;
    }

}
