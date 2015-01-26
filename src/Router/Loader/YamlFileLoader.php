<?php
/**
 * This file is part of the PPI Framework.
 *
 * @copyright  Copyright (c) 2011-2013 Paul Dragoonis <paul@ppi.io>
 * @license    http://opensource.org/licenses/mit-license.php MIT
 * @link       http://www.ppi.io
 */

namespace PPI\Router\Loader;

use Symfony\Component\Routing\Loader\YamlFileLoader as BaseYamlFileLoader;
use Symfony\Component\Routing\RouteCollection;

/**
 * YamlFileLoader class
 *
 * @author     Paul Dragoonis <paul@ppi.io>
 * @author     Vítor Brandão <vitor@ppi.io>
 * @package    PPI
 * @subpackage Module
 */
class YamlFileLoader extends BaseYamlFileLoader
{
    /**
     * The loader defaults
     *
     * @var array
     */
    protected $defaults = array();

    /**
     * Constructor.
     *
     * @param array $defaults
     *
     * @return void
     */
    public function setDefaults($defaults)
    {
        $this->defaults = $defaults;
    }

    /**
     * Parses a route and adds it to the RouteCollection.
     *
     * @param RouteCollection $collection A RouteCollection instance
     * @param string          $name       Route name
     * @param array           $config     Route definition
     * @param string          $path       Full path of the YAML file being processed
     */
    protected function parseRoute(RouteCollection $collection, $name, array $config, $path)
    {
        if (!empty($this->defaults)) {
            $config['defaults'] = array_merge($config['defaults'], $this->defaults);
        }

        parent::parseRoute($collection, $name, $config, $path);
    }
}
