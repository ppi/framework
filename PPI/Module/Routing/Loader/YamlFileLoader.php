<?php
/**
 * This file is part of the PPI Framework.
 *
 * @copyright  Copyright (c) 2012 Paul Dragoonis <paul@ppi.io>
 * @license    http://opensource.org/licenses/mit-license.php MIT
 * @link       http://www.ppi.io
 */
namespace PPI\Module\Routing\Loader;

use Symfony\Component\Routing\Loader\YamlFileLoader as BaseYamlFileLoader,
    Symfony\Component\Routing\RouteCollection;

/**
 * @todo Add inline documentation.
 *
 * @package    PPI
 * @subpackage Module
 */
class YamlFileLoader extends BaseYamlFileLoader
{
    /**
     * @todo Add inline documentation.
     */
    protected $_defaults = array();

    /**
     * @todo Add inline documentation.
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
