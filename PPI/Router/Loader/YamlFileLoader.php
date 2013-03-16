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
 * @todo Add inline documentation.
 *
 * @package    PPI
 * @subpackage Module
 */
class YamlFileLoader extends BaseYamlFileLoader
{
    /**
     * @todo Add inline documentation.
     *
     * @var array
     */
    protected $_defaults = array();

    /**
     * Constructor.
     *
     * @param type $defaults
     *
     * @return void
     */
    public function setDefaults($defaults)
    {
        $this->_defaults = $defaults;
    }

    /**
     * @todo Add inline documentation.
     *
     * @param RouteCollection $collection
     * @param type            $name
     * @param type            $config
     * @param type            $file
     *
     * @return void
     */
    protected function parseRoute(RouteCollection $collection, $name, $config, $file)
    {
        if (!empty($this->_defaults)) {
            $config['defaults'] = array_merge($config['defaults'], $this->_defaults);
        }

        parent::parseRoute($collection, $name, $config, $file);
    }

}
