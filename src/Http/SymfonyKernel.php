<?php

namespace PPI\Framework\Http;

use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Yaml\Yaml;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Loader\IniFileLoader;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Loader\DirectoryLoader;
use Symfony\Component\DependencyInjection\Loader\ClosureLoader;
use Symfony\Component\HttpKernel\Config\FileLocator;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\Config\Loader\DelegatingLoader;

class SymfonyKernel extends BaseKernel
{


    /**
     * @var string
     */
    private $appConfigDir;

    /**
     * @var string
     */
    private $appConfigFile;

    /**
     * @var string
     */
    private $bundlesConfigFile;

    /**
     * @var string
     */
    private $cacheDir;

    /**
     * @var string
     */
    private $logDir;

    /**
     * @param string $dir
     */
    public function setCacheDir($dir)
    {
        $this->cacheDir = $dir;
    }

    /**
     * @param string $dir
     */
    public function setLogDir($dir)
    {
        $this->logDir = $dir;
    }

    /**
     * @param string $configDir
     */
    public function setAppConfigDir($configDir)
    {
        $this->appConfigDir = $configDir;
    }

    /**
     * @param string $appConfigFile
     */
    public function setAppConfigFile($appConfigFile)
    {
        $this->appConfigFile = $appConfigFile;
    }

    /**
     * @param string $bundlesConfigFile
     */
    public function setBundlesConfigFile($bundlesConfigFile)
    {
        $this->bundlesConfigFile = $bundlesConfigFile;
    }

    /**
     * @param string $dir
     */
    public function setRootDir($dir)
    {
        $this->rootDir = $dir;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function registerBundles()
    {
        if(!is_readable($this->bundlesConfigFile)) {
            throw new \Exception('Configuration file for config not found: ' . $this->bundlesConfigFile);
        }

        $config = Yaml::parse($this->bundlesConfigFile);

        if(!isset($config['bundles'])) {
            throw new \Exception('Cannot find any symfony bundles to load');
        }

        $bundlesList = $config['bundles'];

        if(!is_array($bundlesList)) {
            throw new \Exception('Bundles config should be an array, but it is of type: ' . gettype($bundlesList));
        }

        $bundles = [];
        foreach($bundlesList as $bundle) {
            if(!class_exists($bundle)) {
                throw new \Exception('Unable to load bundle class named: ' . $bundle);
            }
            $bundles[] = new $bundle;
        }

        return $bundles;
    }

    /**
     * @param LoaderInterface $loader
     */
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load($this->appConfigFile);
    }

    /**
     * @param ContainerInterface $container
     * @return DelegatingLoader
     */
    protected function getContainerLoader(ContainerInterface $container)
    {
        $locator = new FileLocator($this, $this->appConfigDir);
        $resolver = new LoaderResolver(array(
            new XmlFileLoader($container, $locator),
            new YamlFileLoader($container, $locator),
            new IniFileLoader($container, $locator),
            new PhpFileLoader($container, $locator),
            new ClosureLoader($container),
        ));

        return new DelegatingLoader($resolver);
    }

    /**
     * @return string
     */
    public function getCacheDir()
    {
        return $this->cacheDir;
    }

    /**
     * @return string
     */
    public function getLogDir()
    {
        return $this->logDir;
    }
}