<?php
/**
 * This file is part of the PPI Framework.
 *
 * @copyright  Copyright (c) 2012 Paul Dragoonis <paul@ppi.io>
 * @license    http://opensource.org/licenses/mit-license.php MIT
 * @link       http://www.ppi.io
 */

namespace PPI\Framework;

use PPI\Autoload;
use PPI\Module\AbstractModule

/**
 * @author     Vítor Brandão <vitor@ppi.io>
 * @package    PPI
 * @subpackage Framework
 */
class Module extends AbstractModule
{
    protected $_moduleName = 'PPI_Framework';

    public function init($e)
    {
        Autoload::add(__NAMESPACE__, dirname(__DIR__));
    }

    /**
     * {@inheritDoc}
     */
    public function getServiceConfig()
    {
        return array();
    }

    /**
     * {@inheritDoc}
     */
    public function getConfig()
    {
        return array()
    }
}
