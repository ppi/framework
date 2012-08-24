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

    // Exceptions
    PPI\Exception\Handler as ExceptionHandler,

    // Modules
    PPI\Module\ServiceLocator,
    PPI\Module\Listener\ListenerOptions,
    Zend\ModuleManager\ModuleManager,
    PPI\Module\Listener\DefaultListenerAggregate as PPIDefaultListenerAggregate,

    // Services
    PPI\ServiceManager\ServiceManager,
    PPI\ServiceManager\Config\HttpConfig,
    PPI\ServiceManager\Config\ModuleConfig,
    PPI\ServiceManager\Config\RouterConfig,
    PPI\ServiceManager\Config\TemplatingConfig,
    PPI\ServiceManager\Options\AppOptions,

    // HTTP Stuff and routing

    PPI\Module\Routing\RoutingHelper,
    PPI\Module\Routing\Loader\YamlFileLoader,
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
 * @author Paul Dragoonis <paul@ppi.io>
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
        // app core parameters
        'app.environment'       => 'production',
        'app.debug'             => false,
        'app.root_dir'          => null,
        'app.cache_dir'         => '%app.root_dir%/cache',
        'app.logs_dir'          => '%app.root_dir%/logs',
        'app.module_dirs'       => null,
        'app.modules'           => array(),
        'app.charset'           => 'UTF-8',
        // templating
        'templating.engines'    => array('php'),
        'templating.globals'    => array(),
        // routing
        '404RouteName'          => 'Framework_404',
        // datasource
        'useDataSource'         => false,
        // session
        'sessionclass'          => 'Symfony\Component\HttpFoundation\Session\Session',
        'sessionstorageclass'   => 'Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage'
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

        $this->_options['app.root_dir'] = getcwd().'/app';
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
        
        // Lets setup exception handlers to catch anything that fails during boot as well.
        $exceptionHandler = new ExceptionHandler();
        $exceptionHandler->addHandler(new \PPI\Exception\Log());
        set_exception_handler(array($exceptionHandler, 'handle'));
        
        if($this->getEnv() !== 'production') {
            set_error_handler(array($exceptionHandler, 'handleError'));
        }
        
        if (empty($this->_options['moduleConfig']['listenerOptions'])) {
            throw new \Exception('Missing moduleConfig: listenerOptions');
        }

        $this->serviceManager = new ServiceManager(new AppOptions($this->_options), array(
            new HttpConfig(),
            new ModuleConfig(),
            new RouterConfig(),
            new TemplatingConfig()
        ));
        $this->serviceManager->compile();

        $this->_request  = $this->serviceManager->get('request');
        $this->_response = $this->serviceManager->get('response');

        // Loading our Modules
        $defaultListener = $this->serviceManager->get('module.defaultListener');
        $this->_moduleManager = $this->serviceManager->get('module.manager');
        $this->_moduleManager->loadModules();

        // CONFIG - Merge the app config with the config from all the modules
        $mergedConfig = ArrayUtils::merge(
            $this->_options['config'],
            $defaultListener->getConfigListener()->getMergedConfig(false)
        );
        $this->serviceManager->set('config', $mergedConfig);

        // SERVICES - Lets get all the services our of our modules and start setting them in the ServiceManager
        $moduleServices = $defaultListener->getServices();
        foreach ($moduleServices as $serviceKey => $serviceVal) {
            $this->serviceManager->setFactory($serviceKey, $serviceVal);
        }

        // ROUTING
        $this->_router = $this->serviceManager->get('router');
        $this->handleRouting();

        // DATASOURCE - If the user wants DataSource available in their application, lets instantiate it and set up their connections
        $dsConnections = $this->getAppConfigValue('datasource.connections');
        if ($this->_options['useDataSource'] === true && $dsConnections !== null) {
             $this->serviceManager->set('datasource', new \PPI\DataSource\DataSource($dsConnections));
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
