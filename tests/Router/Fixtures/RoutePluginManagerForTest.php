<?php
/**
 * This file is part of the PPI Framework.
 *
 * @copyright  Copyright (c) 2011-2015 Paul Dragoonis <paul@ppi.io>
 * @license    http://opensource.org/licenses/mit-license.php MIT
 *
 * @link       http://www.ppi.io
 */

namespace PPI\FrameworkTest\Router\Fixtures;

use PPI\Framework\Router\RoutePluginManager;

/**
 * Class RoutePluginManagerForTest.
 *
 * @author Vítor Brandão <vitor@ppi.io>
 */
class RoutePluginManagerForTest extends RoutePluginManager
{
    /**
     * {@inheritDoc}
     */
    public function validatePlugin($plugin)
    {
        return;
    }
}
