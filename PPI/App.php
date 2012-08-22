<?php
/**
 * This file is part of the PPI Framework.
 *
 * @category    PPI
 * @package     Core
 * @copyright   Copyright (c) 2012 Paul Dragoonis <paul@ppi.io>
 * @license     http://opensource.org/licenses/mit-license.php MIT
 * @link        http://www.ppi.io
 */

namespace PPI;

use

    // Modules
    PPI\Module\ServiceLocator,
    PPI\Module\Listener\ListenerOptions,
    Zend\ModuleManager\ModuleManager,
    PPI\Module\Listener\DefaultListenerAggregate as PPIDefaultListenerAggregate,
    
    // Services
    PPI\ServiceManager\ServiceManager,
    PPI\ServiceManager\Config\HttpConfig,
    PPI\ServiceManager\Config\ModuleConfig,
    PPI\ServiceManager\Config\OptionsConfig,
    PPI\ServiceManager\Config\TemplatingConfig,

    // HTTP Stuff and routing
    PPI\Module\Routing\Router,
    PPI\Module\Routing\RoutingHelper,
    PPI\Module\Routing\Loader\YamlFileLoader,
    Symfony\Component\Routing\RequestContext,
    Symfony\Component\Routing\RouteCollection,
    Symfony\Component\Routing\Generator\UrlGenerator,
    Symfony\Component\HttpFoundation\Request as HttpRequest,
    Symfony\Component\HttpFoundation\Response as HttpResponse,
    Symfony\Component\Routing\Exception\ResourceNotFoundException,

    // Misc
    Zend\Stdlib\ArrayUtils;



/**
 * The PPI App bootstrap class.
 *
 * This class sets various app settings, and allows you to override clases used
 * in the bootup process.
 *
 * @author Paul Dragoonis <dragoonis@php.net>
 * @author Vítor Brandão <vitor@ppi.io>
 */
class App
{
    /**
     * Default options for the app.
     *
     * @var array
     */
    protected $_options = array(
        'templating.engines'   => array('php'),
        '404RouteName'        => 'Framework_404',
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
     * Service configuration
     *
     * @var \PPI\Module\ServiceManager\ServiceConfig
     */
     protected $serviceConfig;

    /**
     * Service Locator
     *
     * @deprecated by $serviceManager
     * @var null|\PPI\Module\ServiceLocator
     */
    protected $_serviceLocator = null;

    /**
     * The constructor.
     *
     * @param array $options
     */

    public function __construct(array $options = array())
    {
        if (!empty($options)) {
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
     * @param  array $options The options
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
     * @return $this Fluent interface
     */
    public function boot()
    {
        if (empty($this->_options['moduleConfig']['listenerOptions'])) {
            throw new \Exception('Missing moduleConfig: listenerOptions');
        }

        $this->serviceManager = new ServiceManager(array(
            new OptionsConfig($this->_options),
            new HttpConfig(),
            new ModuleConfig(),
            new TemplatingConfig()
        ));

        $this->_request  = $this->serviceManager->get('request');
        $this->_response = $this->serviceManager->get('response');

        $routerOptions = array();
        if ($this->getAppConfigValue('cache_dir') !== null) {
            $routerOptions['cache_dir'] = $this->getAppConfigValue('cache_dir');
        }

        // Initialise the routing components
        $routingEnabled  = true;
        $matchedRoute    = false;
        $routeCollection = new RouteCollection();
        $requestContext  = new RequestContext();
        $requestContext->fromRequest($this->_request);

        $this->_router = new Router($requestContext, $routeCollection, $routerOptions);

        if (!$this->isDevMode()) {

            // If we are in production mode, and have the routing file(s) have been cached, then skip route fetching on modules boot
            if ($this->_router->isGeneratorCached() && $this->_router->isMatcherCached()) {
                $this->_options['moduleConfig']['listenerOptions']['routingEnabled'] = false;
                $routingEnabled = false;
            }

        }

        // Loading our Modules
        $defaultListener = $this->serviceManager->get('module.defaultListener');
        $this->_moduleManager = $this->serviceManager->get('module.manager');
        $this->_moduleManager->loadModules();
        
        // CONFIG - Merge the app config with the config from all the modules
        $mergedConfig = ArrayUtils::merge(
            $this->_options['config'],
            $defaultListener->getConfigListener()->getMergedConfig(false)
        );
        
        // SERVICES - Lets get all the services our of our modules and start setting them in the ServiceManager
        $moduleServices = $defaultListener->getServices();
        foreach($moduleServices as $serviceKey => $serviceVal) {
            $this->serviceManager->setFactory($serviceKey, $serviceVal);
        }

        // ROUTING - If the routing process for modules has been cached or not.
        if ($routingEnabled) {

            // Merging all the other route collections together from the modules
            $allRoutes = $defaultListener->getRoutes();
            foreach ($allRoutes as $routes) {
                $routeCollection->addCollection($routes);
            }
            $this->_router->setRouteCollection($routeCollection);
        }

        $this->handleRouting();

        $this->serviceManager->set('session', $this->getSession());
        $this->serviceManager->set('config', $mergedConfig);
        $this->serviceManager->set('router', $this->_router);
        
        // DATASOURCE - If the user wants DataSource available in their application, lets instantiate it and set up their connections
        $dsConnections = $this->getAppConfigValue('datasource.connections');
        if ($this->_options['useDataSource'] === true && $dsConnections !== null) {
            $defaultServices['dataSource'] = $this->getDataSource($dsConnections);
        }

        // Fluent Interface
        return $this;
    }

    /**
     * Lets dispatch our module's controller action
     */
    public function dispatch()
    {

        // Lets disect our route
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
        unset($routeParams['_module'], $routeParams['_controller'], $routeParams['_route']);
        $controller->setHelper('routing', new RoutingHelper($routeParams));

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

    /**
     * Match a route based on the specified $uri.
     * Set up _matchedRoute and _matchedModule too
     *
     * @param  string $uri
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
     *
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
                $routeUri = $this->_router->generate($this->_options['404RouteName']);

                // If the base url is preset before the $routeUri, get rid of it
                if ( ($pos = strpos($routeUri, $baseUrl)) !== false ) {
                    $routeUri = substr_replace($routeUri, '', $pos, strlen($baseUrl));
                }

                $this->matchRoute($routeUri);

            // @todo handle a 502 here
            } catch (\Exception $e) {
                die('Unable to load 404 page. An internal error occured');
            }

        }

    }

    /**
     * Instantiate the DataSource component, optionally taking in its connections
     *
     * @param  array                 $connections
     * @return DataSource\DataSource
     */
    protected function getDataSource(array $connections = array())
    {
        return new \PPI\DataSource\DataSource($connections);
    }

    /**
     * Get the session class
     *
     * @return \Symfony\Component\HttpFoundation\Session\Session
     */
    protected function getSession()
    {
        if ($this->session === null) {
            $session = new $this->_options['sessionclass'](new $this->_options['sessionstorageclass']());
            $session->start();
            $this->session = $session;
        }

        return $this->session;
    }

    /**
     * Get an option
     *
     * @param $key
     * @param  null $default
     * @return null
     */
    public function getOption($key, $default = null)
    {
        return isset($this->_options[$key]) ? $this->_options[$key] : $default;
    }

    /**
     * Get a config value from the application config
     *
     * @param  string $key
     * @param  mixed  $default
     * @return mixed
     */
    public function getAppConfigValue($key, $default = null)
    {
        return isset($this->_options['config'][$key]) ? $this->_options['config'][$key] : $default;
    }

    /**
     * Set the option
     *
     * @param $key
     * @param $val
     */
    public function setOption($key, $val)
    {
        $this->_options[$key] = $val;
    }

    /**
     * Magic setter function, this is an alias of setOption
     *
     * @param  string $option The Option
     * @param  string $value  The Value
     * @return void
     */
    public function __set($option, $value)
    {
        $this->_options[$option] = $value;
    }

    /**
     * Get the environment mode the application is in.
     *
     * @return string
     */
    public function getEnv()
    {
        return $this->getAppConfigValue('environment');
    }

    /**
     * Check if the application is in development mode.
     *
     * @return bool
     */
    public function isDevMode()
    {
        return $this->getEnv() === 'development';
    }

    /**
     * Magic getter function, this is an alias of getEnv()
     *
     * @param  string $option The Option
     * @return mixed
     */
    public function __get($option)
    {
        return isset($this->_options[$option]) ? $this->_options[$option] : null;
    }

}
