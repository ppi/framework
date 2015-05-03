<?php
/**
 * This file is part of the PPI Framework.
 *
 * @copyright  Copyright (c) 2011-2015 Paul Dragoonis <paul@ppi.io>
 * @license    http://opensource.org/licenses/mit-license.php MIT
 *
 * @link       http://www.ppi.io
 */

namespace PPI\FrameworkTest\Router;

use PPI\Framework\Router\ChainRouter;

/**
 * Class ChainRouterTest
 *
 * @author Paul Dragoonis <paul@ppi.io>
 */
class ChainRouterTest extends \PHPUnit_Framework_TestCase
{
    private $router = null;

    protected function setUp()
    {
        $this->router = new ChainRouter();
    }

    /**
     * @dataProvider parametersToStringData
     */
    public function testParametersToString($parameters, $expected)
    {
        $this->assertEquals($expected, $this->router->parametersToString($parameters));
    }

    /**
     * @return array
     */
    public function parametersToStringData()
    {
        return array(
            array(array('_controller' => 'index'), '"_controller": "index"'),
            array(array('_controller' => 'index'), '"_controller": "index"'),
            array(array('_module' => 'Application'), '"_module": "Application"')
        );
    }
}


