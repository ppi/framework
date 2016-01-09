<?php
/**
 * This file is part of the PPI Framework.
 *
 * @copyright  Copyright (c) 2011-2015 Paul Dragoonis <paul@ppi.io>
 * @license    http://opensource.org/licenses/mit-license.php MIT
 *
 * @link       http://www.ppi.io
 */

namespace PPI\Framework;

use PPI\Framework\Config\ConfigManager;
use PPI\Framework\Debug\ExceptionHandler;
use PPI\Framework\Http\Request as HttpRequest;
use PPI\Framework\Http\Response as HttpResponse;
use PPI\Framework\ServiceManager\ServiceManagerBuilder;
use Symfony\Component\ClassLoader\DebugClassLoader;
use Symfony\Component\Debug\ErrorHandler;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

// @todo - these need moved to a new class for module->router processing
use PPI\Framework\Router\Router as SymfonyRouter;
use PPI\Framework\Router\Wrapper\AuraRouterWrapper;
use PPI\Framework\Router\Wrapper\SymfonyRouterWrapper;
use Aura\Router\Router as AuraRouter;
use Illuminate\Http\Request as LaravelRequest;
use Illuminate\Routing\Router as LaravelRouter;
use Illuminate\Routing\UrlGenerator as LaravelUrlGenerator;
use PPI\FastRoute\Wrapper\FastRouteWrapper;
use PPI\LaravelRouting\Wrapper\LaravelRouterWrapper;

/**
 * The PPI App bootstrap class.
 *
 * This class sets various app settings, and allows you to override classes used in the bootup process.
 *
 * @author     Paul Dragoonis <paul@ppi.io>
 * @author     Vítor Brandão <vitor@ppi.io>
 */
class App implements AppInterface
{
    /**
     * Version string.
     *
     * @var string
     */
    const VERSION = '2.1.0-DEV';

    /**
     * @var bool
     */
    protected $booted = false;

    /**
     * @var bool
     */
    protected $debug;

    /**
     * Application environment: "dev|development" vs "prod|production".
     *
     * @var string
     */
    protected $environment;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * Unix timestamp with microseconds.
     *
     * @var float
     */
    protected $startTime;

    /**
     * Configuration loader.
     *
     * @var \PPI\Framework\Config\ConfigManager
     */
    protected $configManager;

    /**
     * The Module Manager.
     *
     * @var \Zend\ModuleManager\ModuleManager
     */
    protected $moduleManager;

    /**
     * @param int $errorReportingLevel The level of error reporting you want
     */
    protected $errorReportingLevel;

    /**
     * @var null|array
     */
    protected $matchedRoute;

    /**
     * @var \PPI\Framework\Module\Controller\ControllerResolver
     */
    protected $resolver;

    /**
     * @var string
     */
    protected $name;

    /**
     * Path to the application root dir aka the "app" directory.
     *
     * @var null|string
     */
    protected $rootDir;

    /**
     * Service Manager.
     *
     * @var \PPI\Framework\ServiceManager\ServiceManager
     */
    protected $serviceManager;

    /**
     * App constructor.
     *
     * @param array $options
     */
    public function __construct(array $options = array())
    {
        // Default options
        $this->environment = isset($options['environment']) && $options['environment'] ? (string) $options['environment'] : 'prod';
        $this->debug = isset($options['debug']) && null !== $options['debug'] ? (boolean) $options['debug'] : false;
        $this->rootDir = isset($options['rootDir']) && $options['rootDir'] ? (string) $options['rootDir'] : $this->getRootDir();
        $this->name = isset($options['name']) && $options['name'] ? (string) $options['name'] : $this->getName();

        if ($this->debug) {
            $this->startTime = microtime(true);
            $this->enableDebug();
        } else {
            ini_set('display_errors', 0);
        }
    }

    /**
     * Set an App option.
     *
     * @param $option
     * @param $value
     *
     * @throws \RuntimeException
     *
     * @return $this
     */
    public function setOption($option, $value)
    {
        if (true === $this->booted) {
            throw new \RuntimeException('Setting App options after boot() is now allowed');
        }

        // "root_dir" to "rootDir"
        $property = preg_replace('/_(.?)/e', "strtoupper('$1')", $option);
        if (!property_exists($this, $property)) {
            throw new \RuntimeException(sprintf('App property "%s" (option "%s") does not exist', $property, $option));
        }

        $this->$property = $value;

        return $this;
    }

    /**
     * Get an App option.
     *
     * @param $option
     *
     * @throws \RuntimeException
     *
     * @return string
     */
    public function getOption($option)
    {
        // "root_dir" to "rootDir"
        $property = preg_replace('/_(.?)/e', "strtoupper('$1')", $option);
        if (!property_exists($this, $property)) {
            throw new \RuntimeException(sprintf('App property "%s" (option "%s") does not exist', $property, $option));
        }

        return $property;
    }

    public function __clone()
    {
        if ($this->debug) {
            $this->startTime = microtime(true);
        }

        $this->booted = false;
        $this->serviceManager = null;
    }

    /**
     * Run the boot process, load our modules and their dependencies.
     *
     * This method is automatically called by dispatch(), but you can use it
     * to build all services when not handling a request.
     *
     * @return $this
     */
    public function boot()
    {
        if (true === $this->booted) {
            return $this;
        }

        $this->serviceManager = $this->buildServiceManager();
        $this->log('debug', sprintf('Booting %s ...', $this->name));

        // Loading our Modules
        $this->getModuleManager()->loadModules();
        if ($this->debug) {
            $modules = $this->getModuleManager()->getModules();
            $this->log('debug', sprintf('All modules online (%d): "%s"', count($modules), implode('", "', $modules)));
        }

        // Lets get all the services our of our modules and start setting them in the ServiceManager
        $moduleServices = $this->serviceManager->get('ModuleDefaultListener')->getServices();
        foreach ($moduleServices as $key => $service) {
            $this->serviceManager->setFactory($key, $service);
        }

        $this->booted = true;
        if ($this->debug) {
            $this->log('debug', sprintf('%s has booted (in %.3f secs)', $this->name, microtime(true) - $this->startTime));
        }

        return $this;
    }

    /**
     * Run the application and send the response.
     *
     * @param HttpRequest|null $request
     * @param HttpRequest|null $request
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function run(HttpRequest $request = null, HttpResponse $response = null)
    {
        if (false === $this->booted) {
            $this->boot();
        }

        $request = $request ?: HttpRequest::createFromGlobals();
        $response = $response ?: new HttpResponse();

        $response = $this->dispatch($request, $response);
        $response->send();

        return $response;
    }

    /**
     * Decide on a route to use and dispatch our module's controller action.
     *
     * @param HttpRequest  $request
     * @param HttpResponse $response
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function dispatch(HttpRequest $request, HttpResponse $response)
    {
        if (false === $this->booted) {
            $this->boot();
        }

        // Routing
        $routeParams = $this->handleRouting($request);
        $request->attributes->add($routeParams);

        // Resolve our Controller
        $resolver = $this->serviceManager->get('ControllerResolver');
        if (false === $controller = $resolver->getController($request)) {
            throw new NotFoundHttpException(sprintf('Unable to find the controller for path "%s".', $request->getPathInfo()));
        }

        $controllerArguments = $resolver->getArguments($request, $controller);

        $result = call_user_func_array(
            $controller,
            $controllerArguments
        );

        if ($result === null) {
            throw new \Exception('Your action returned null. It must always return something');
        } elseif (is_string($result)) {
            $response->setContent($result);
        } elseif ($result instanceof SymfonyResponse || $response instanceof HttpResponse) {
            $response = $result;
        } else {
            throw new \Exception('Invalid response type returned from controller');
        }

        $this->response = $response;

        return $response;
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
     * Gets the version of the application.
     *
     * @return string The application version
     *
     * @api
     */
    public function getVersion()
    {
        return self::VERSION;
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
     * @param $env
     *
     * @return bool
     */
    public function isEnvironment($env)
    {
        if ('development' == $env) {
            $env = 'dev';
        } elseif ('production' == $env) {
            $env = 'prod';
        }

        return $this->getEnvironment() == $env;
    }

    /**
     * Checks if debug mode is enabled.
     *
     * @return bool true if debug mode is enabled, false otherwise
     *
     * @api
     */
    public function isDebug()
    {
        return $this->debug;
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
     * Get the service manager.
     *
     * @return ServiceManager\ServiceManager
     */
    public function getServiceManager()
    {
        return $this->serviceManager;
    }

    /**
     * @note Added for compatibility with Symfony's HttpKernel\Kernel.
     *
     * @return null|ServiceManager\ServiceManager
     */
    public function getContainer()
    {
        return $this->serviceManager;
    }

    /**
     * Returns the Module Manager.
     *
     * @return \Zend\ModuleManager\ModuleManager
     */
    public function getModuleManager()
    {
        if (null === $this->moduleManager) {
            $this->moduleManager = $this->serviceManager->get('ModuleManager');
        }

        return $this->moduleManager;
    }

    /**
     * Get an array of the loaded modules.
     *
     * @return array An array of Module objects, keyed by module name
     */
    public function getModules()
    {
        return $this->getModuleManager()->getLoadedModules(true);
    }

    /**
     * @see PPI\Framework\Module\ModuleManager::locateResource()
     *
     * @param string $name  A resource name to locate
     * @param string $dir   A directory where to look for the resource first
     * @param bool   $first Whether to return the first path or paths for all matching bundles
     *
     * @throws \InvalidArgumentException if the file cannot be found or the name is not valid
     * @throws \RuntimeException         if the name contains invalid/unsafe
     * @throws \RuntimeException         if a custom resource is hidden by a resource in a derived bundle
     *
     * @return string|array The absolute path of the resource or an array if $first is false
     */
    public function locateResource($name, $dir = null, $first = true)
    {
        return $this->getModuleManager()->locateResource($name, $dir, $first);
    }

    /**
     * Gets the request start time (not available if debug is disabled).
     *
     * @return int The request start timestamp
     *
     * @api
     */
    public function getStartTime()
    {
        return $this->debug ? $this->startTime : -INF;
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
        return $this->rootDir . '/cache/' . $this->environment;
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
        return $this->rootDir . '/logs';
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
     * Returns a ConfigManager instance.
     *
     * @return \PPI\Framework\Config\ConfigManager
     */
    public function getConfigManager()
    {
        if (null === $this->configManager) {
            $cachePath = $this->getCacheDir() . '/application-config-cache.' . $this->getName() . '.php';
            $this->configManager = new ConfigManager($cachePath, !$this->debug, $this->rootDir . '/config');
        }

        return $this->configManager;
    }

    /**
     * Loads a configuration file or PHP array.
     *
     * @param  $resource
     * @param null $type
     *
     * @return App The current instance
     */
    public function loadConfig($resource, $type = null)
    {
        $this->getConfigManager()->addConfig($resource, $type);

        return $this;
    }

    /**
     * Returns the application configuration.
     *
     * @throws \RuntimeException
     *
     * @return array|object
     */
    public function getConfig()
    {
        if (!$this->booted) {
            throw new \RuntimeException('The "Config" service is only available after the App boot()');
        }

        return $this->serviceManager->get('Config');
    }

    public function serialize()
    {
        return serialize(array($this->environment, $this->debug));
    }

    public function unserialize($data)
    {
        list($environment, $debug) = unserialize($data);

        $this->__construct($environment, $debug);
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
                'app.root_dir' => $this->rootDir,
                'app.environment' => $this->environment,
                'app.debug' => $this->debug,
                'app.name' => $this->name,
                'app.cache_dir' => $this->getCacheDir(),
                'app.logs_dir' => $this->getLogDir(),
                'app.charset' => $this->getCharset(),
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
     * Creates and initializes a ServiceManager instance.
     *
     * @return ServiceManager The compiled service manager
     */
    protected function buildServiceManager()
    {
        // ServiceManager creation
        $serviceManager = new ServiceManagerBuilder($this->getConfigManager()->getMergedConfig());
        $serviceManager->build($this->getAppParameters());
        $serviceManager->set('app', $this);

        return $serviceManager;
    }

    /**
     * Perform the matching of a route and return a set of routing parameters if a valid one is found.
     * Otherwise exceptions get thrown.
     *
     * @param HttpRequest $request
     *
     * @throws \Exception
     *
     * @return array
     */
    protected function handleRouting(HttpRequest $request)
    {
        $this->router = $this->serviceManager->get('Router');
        $this->router->warmUp($this->getCacheDir());

        try {
            // Lets load up our router and match the appropriate route
            $parameters = $this->router->matchRequest($request);
            if (!empty($parameters)) {
                if (null !== $this->logger) {
                    $this->logger->info(sprintf('Matched route "%s" (parameters: %s)', $parameters['_route'], $this->router->parametersToString($parameters)));
                }
            }
        } catch (ResourceNotFoundException $e) {
            $routeUri = $this->router->generate('Framework_404');
            $parameters = $this->router->matchRequest($request::create($routeUri));
        } catch (\Exception $e) {
            throw $e;
        }

        $parameters['_route_params'] = $parameters;

        return $parameters;
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed  $level
     * @param string $message
     * @param array  $context
     */
    protected function log($level, $message, array $context = array())
    {
        if (null === $this->logger && $this->getServiceManager()->has('logger')) {
            $this->logger = $this->getServiceManager()->get('logger');
        }

        if ($this->logger) {
            $this->logger->log($level, $message, $context);
        }
    }

    /**
     * Enables the debug tools.
     *
     * This method registers an error handler and an exception handler.
     *
     * If the Symfony ClassLoader component is available, a special
     * class loader is also registered.
     */
    protected function enableDebug()
    {
        error_reporting(-1);

        ErrorHandler::register($this->errorReportingLevel);
        if ('cli' !== php_sapi_name()) {
            $handler = ExceptionHandler::register();
            $handler->setAppVersion($this->getVersion());
        } elseif (!ini_get('log_errors') || ini_get('error_log')) {
            ini_set('display_errors', 1);
        }

        if (class_exists('Symfony\Component\ClassLoader\DebugClassLoader')) {
            DebugClassLoader::enable();
        }
    }

    protected function setupRouters()
    {

        $requestContext  = $this->getServiceManager()->get('RouterRequestContext');
        $chainRouter->setContext($requestContext);

        $allModuleRoutes = $this->getServiceManager()->get('ModuleDefaultListener')->getRoutes();

        // For each module, add a matching instance type to the chain router
        foreach ($allModuleRoutes as $moduleName => $moduleRoutingResponse) {
            switch (true) {
                // @todo - move this to a separate method()
                case $moduleRoutingResponse instanceof SymfonyRouteCollection:
                    $sfRouter = new SymfonyRouter($requestContext, $moduleRoutingResponse, $routerOptions, $logger);
                    $sfRouterWrapper = new SymfonyRouterWrapper($sfRouter);
                    $chainRouter->add($sfRouterWrapper);
                    break;

                // @todo - move this to a separate method()
                case $moduleRoutingResponse instanceof AuraRouter:
                    $auraRouterWrapper = new AuraRouterWrapper($moduleRoutingResponse);
                    $chainRouter->add($auraRouterWrapper);
                    break;

                // @todo - move this to a separate method()
                case $moduleRoutingResponse instanceof LaravelRouter:
                    $laravelRequest = new LaravelRequest();
                    $laravelUrlGenerator = new LaravelUrlGenerator($moduleRoutingResponse->getRoutes(), $laravelRequest);
                    $laravelRouterWrapper = new LaravelRouterWrapper(
                        $moduleRoutingResponse, $laravelRequest, $laravelUrlGenerator
                    );
                    // @todo - solve this problem
//                    $laravelRouterWrapper->setModuleName($this->getName());
                    $chainRouter->add($laravelRouterWrapper);
                    break;

                case $moduleRoutingResponse instanceof FastRouteWrapper:
                    $chainRouter->add($moduleRoutingResponse);
                    break;

                default:
                    throw new \Exception('Unexpected routes value return from module: ' . $moduleName .
                        '. found value of type: ' . gettype($moduleRoutingResponse));
            }
        }
    }


}
