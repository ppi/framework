<?php
/**
 * This file is part of the PPI Framework.
 *
 * @copyright  Copyright (c) 2012 Paul Dragoonis <paul@ppi.io>
 * @license    http://opensource.org/licenses/mit-license.php MIT
 * @link       http://www.ppi.io
 */

namespace PPI\Config;

use PPI\Config\FileLocator;
use PPI\Config\Loader\DelegatingLoader;
use PPI\Config\Loader\LoaderResolver;
use PPI\Config\Loader\ArrayLoader;
use PPI\Config\Loader\IniFileLoader;
use PPI\Config\Loader\PhpFileLoader;
use PPI\Config\Loader\YamlFileLoader;

/**
 * FileLocator uses an array of pre-defined paths to find files.
 *
 * @author     Vítor Brandão <vitor@ppi.io>
 * @package    PPI
 * @subpackage Config
 */
class ConfigLoader
{
     protected $paths;
     protected $loader;

    /**
     * Constructor.
     *
     * @param string|array $paths A path or an array of paths where to look for resources
     */
    public function __construct($paths = array())
    {
        $this->paths = (array) $paths;
    }

    public function load($resource)
    {
        return $this->getConfigLoader()->load($resource);
    }

    /**
     * Returns a loader to handle config loading.
     *
     * @return DelegatingLoader The loader
     */
    protected function getConfigLoader()
    {
        if (null === $this->loader) {
            $locator = new FileLocator($this->paths);
            $resolver = new LoaderResolver(array(
                new YamlFileLoader($locator),
                new PhpFileLoader($locator),
                new IniFileLoader($locator),
                new ArrayLoader(),
            ));

            $this->loader = new DelegatingLoader($resolver);
        }

        return $this->loader;
    }
}
