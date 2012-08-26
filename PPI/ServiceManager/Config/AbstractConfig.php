<?php
/**
 * This file is part of the PPI Framework.
 *
 * @category    PPI
 * @package     ServiceManager
 * @copyright   Copyright (c) 2012 Paul Dragoonis <paul@ppi.io>
 * @license     http://opensource.org/licenses/mit-license.php MIT
 * @link        http://www.ppi.io
 */

namespace PPI\ServiceManager\Config;

use Zend\ServiceManager\Config;
use Zend\ServiceManager\ServiceManager;

/**
 * AbstractConfig class.
 *
 * @author Vítor Brandão <vitor@ppi.io>
 */
abstract class AbstractConfig extends Config
{
    /**
     * Returns the options used to configure the services built in this Config.
     *
     * @return array
     */
    abstract public function getDefaultOptions();
}
