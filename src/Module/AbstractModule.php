<?php
/**
 * This file is part of the PPI Framework.
 *
 * @copyright  Copyright (c) 2011-2016 Paul Dragoonis <paul@ppi.io>
 * @license    http://opensource.org/licenses/mit-license.php MIT
 *
 * @link       http://www.ppi.io
 */

namespace PPI\Framework\Module;

use Aura\Router\Router as AuraRouter;
use Aura\Router\RouterFactory as AuraRouterFactory;
use Illuminate\Events\Dispatcher;
use PPI\FastRoute\Wrapper\FastRouteWrapper;
use PPI\Framework\Config\ConfigLoader;
use PPI\Framework\Console\Application;
use PPI\Framework\Router\Loader\YamlFileLoader;
use PPI\LaravelRouting\LaravelRouter;
use PPI\LaravelRouting\Loader\LaravelRoutesLoader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Finder\Finder;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\Stdlib\ArrayUtils;

/**
 * The base PPI module class.
 *
 * @author Paul Dragoonis <paul@ppi.io>
 */
abstract class AbstractModule implements ModuleInterface, ConfigProviderInterface
{
    /**
     * @var string The Module name.
     */
    protected $name;

    /**
     * @var \ReflectionObject
     */
    protected $reflected;

    /**
     * @todo Add inline documentation.
     *
     * @var null
     */
    protected $config;

    /**
     * Configuration loader.
     *
     * @var null|\PPI\Framework\Config\ConfigLoader
     */
    protected $configLoader;

    /**
     * @todo Add inline documentation.
     *
     * @var null
     */
    protected $routes;

    /**
     * @todo Add inline documentation.
     *
     * @var null
     */
    protected $services;

    /**
     * Load up our routes.
     *
     * @param string $path
     *
     * @return \Symfony\Component\Routing\RouteCollection
     */
    protected function loadSymfonyRoutes($path)
    {
        if ($this->routes === null) {
            $loader = new YamlFileLoader(new FileLocator(array(dirname($path))));
            $loader->setDefaults(array('_module' => $this->getName()));

            $routesCollection = $loader->load(pathinfo($path, PATHINFO_FILENAME) . '.' . pathinfo($path, PATHINFO_EXTENSION));
            $this->routes     = $routesCollection;
        }

        return $this->routes;
    }

    /**
     * Load up our routes.
     *
     * @deprecated Please use loadSymfonyRoutes instead
     *
     * @param string $path
     *
     * @return \Symfony\Component\Routing\RouteCollection
     */
    protected function loadYamlRoutes($path)
    {
        return $this->loadSymfonyRoutes($path);
    }

    /**
     * @param string $path
     *
     * @throws \Exception when the included routes file doesn't return an AuraRouter back
     *
     * @return AuraRouter
     */
    protected function loadLaravelRoutes($path)
    {
        $router = (new LaravelRoutesLoader(
            new LaravelRouter(new Dispatcher())
        ))->load($path);
        $router->setModuleName($this->getName());

        return $router;
    }

    /**
     * @param $path
     *
     * @return FastRouteWrapper
     */
    protected function loadFastRouteRoutes($path)
    {
        $routeParser = new \FastRoute\RouteParser\Std();
        $dataGenerator = new \FastRoute\DataGenerator\GroupCountBased();
        $routeCollector = new \FastRoute\RouteCollector($routeParser, $dataGenerator);

        if (!is_readable($path)) {
            throw new \InvalidArgumentException('Invalid fast route routes path found: ' . $path);
        }

        // The included file must return the laravel router
        $getRouteCollector = function () use ($routeCollector, $path) {
            $r = $routeCollector;
            include $path;

            return $r;
        };

        $routeCollector = $getRouteCollector();
        if (!($routeCollector instanceof \FastRoute\RouteCollector)) {
            throw new \Exception('Invalid return value from '
                . pathinfo($path, PATHINFO_FILENAME)
                . ' expected instance of RouteCollector'
            );
        }

        $dispatcher = new \FastRoute\Dispatcher\GroupCountBased($routeCollector->getData());
        $router = new \PPI\FastRoute\Wrapper\FastRouteWrapper(
            $dispatcher
        );
        $router->setModuleName($this->getName());

        return $router;
    }

    /**
     * @todo - move part of this into AuraRouterWrapper->load()
     * @todo - consider adding a setModuleName() to the AuraRouterWrapper instead of _module to each route.
     *
     * @param string $path
     *
     * @throws \Exception when the included routes file doesn't return an AuraRouter back
     *
     * @return AuraRouter
     */
    protected function loadAuraRoutes($path)
    {
        if (!is_readable($path)) {
            throw new \InvalidArgumentException('Invalid aura routes path found: ' . $path);
        }

        $router = (new AuraRouterFactory())->newInstance();

        // The included file must return the aura router
        $router = include $path;

        if (!($router instanceof AuraRouter)) {
            throw new \Exception('Invalid return value from '
                . pathinfo($path, PATHINFO_FILENAME)
                . ' expected instance of AuraRouter'
            );
        }

        foreach ($router->getRoutes() as $route) {
            $route->addValues(array('_module' => $this->getName()));
        }

        return $router;
    }

    /**
     * Load up our config results from the specific yaml file.
     *
     * @param string $path
     *
     * @return array
     *
     * @deprecated since version 2.1, to be removed in 2.2. Use "loadConfig()" instead.
     */
    protected function loadYamlConfig($path)
    {
        return $this->loadConfig($path);
    }

    /**
     * Set services for our module.
     *
     * @param string $services
     *
     * @return Module
     */
    public function setServices($services)
    {
        $this->services = $services;

        return $this;
    }

    /**
     * Get the services.
     *
     * @return array
     */
    public function getServices()
    {
        return $this->services;
    }

    /**
     * Get a particular service.
     *
     * @param string $serviceName
     *
     * @return mixed
     */
    public function getService($serviceName)
    {
        return isset($this->services[$serviceName]) ? $this->services : null;
    }

    /**
     * Loads a configuration file (PHP, YAML) or PHP array.
     *
     * @param string      $resource The resource
     * @param null|string $type     The resource type
     *
     * @return array
     */
    public function loadConfig($resource, $type = null)
    {
        return $this->getConfigLoader()->load($resource, $type);
    }

    /**
     * Loads and merges the configuration.
     *
     * @param mixed $resources
     *
     * @return array
     */
    public function mergeConfig($resources)
    {
        $configs = array();
        foreach (is_array($resources) ? $resources : func_get_args() as $resource) {
            $configs = ArrayUtils::merge($configs, $this->loadConfig($resource));
        }

        return $configs;
    }

    /**
     * Set the module name.
     *
     * @param string $Name
     *
     * @return $this
     */
    public function setName($Name)
    {
        $this->name = $Name;

        return $this;
    }

    /**
     * Returns the module name. Defaults to the module namespace stripped of backslashes.
     *
     * @return string The Module name
     */
    public function getName()
    {
        if (null !== $this->name) {
            return $this->name;
        }

        $this->name = str_replace('\\', '', $this->getNamespace());

        return $this->name;
    }

    /**
     * Gets the Module namespace.
     *
     * @return string The Module namespace
     *
     * @api
     */
    public function getNamespace()
    {
        if (null === $this->reflected) {
            $this->reflected = new \ReflectionObject($this);
        }

        return $this->reflected->getNamespaceName();
    }

    /**
     * Gets the Module directory path.
     *
     * @return string The Module absolute path
     *
     * @api
     */
    public function getPath()
    {
        if (null === $this->reflected) {
            $this->reflected = new \ReflectionObject($this);
        }

        return dirname($this->reflected->getFileName());
    }

    /**
     * Returns configuration to merge with application configuration.
     *
     * @return array|\Traversable
     */
    public function getConfig()
    {
        return array();
    }

    /**
     * Returns the default location of where Command classes are registered.
     * Override this in your child module if it differs from this default convention.
     *
     * @return string
     */
    public function getCommandsPath()
    {
        return sprintf("%s/src/Command", $this->getPath());
    }


    /**
     * Finds and registers Commands.
     *
     * Override this method if your module commands do not follow the conventions:
     *
     * * Commands are in the 'Command' sub-directory
     * * Commands extend PPI\Framework\Console\Command\AbstractCommand
     *
     * @param Application $application An Application instance
     */
    public function registerCommands(Application $application)
    {

        if (!is_dir($dir = $this->getCommandsPath())) {
            return;
        }

        $finder = new Finder();
        $finder->files()->name('*Command.php')->in($dir);

        $prefix = $this->getNamespace() . '\\Command';
        foreach ($finder as $file) {
            $ns = $prefix;
            if ($relativePath = $file->getRelativePath()) {
                $ns .= '\\' . strtr($relativePath, '/', '\\');
            }
            $r = new \ReflectionClass($ns . '\\' . $file->getBasename('.php'));
            if ($r->isSubclassOf('PPI\Framework\\Console\\Command\\AbstractCommand') && !$r->isAbstract()) {
                $application->add($r->newInstance());
            }
        }
    }

    /**
     * Returns a ConfigLoader instance.
     *
     * @return \PPI\Framework\Config\ConfigLoader
     */
    protected function getConfigLoader()
    {
        if (null === $this->configLoader) {
            $this->configLoader = new ConfigLoader($this->getPath() . '/resources/config');
        }

        return $this->configLoader;
    }
}
