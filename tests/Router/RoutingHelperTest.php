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

use PPI\Framework\Router\RoutingHelper;

/**
 * Class RoutingHelperTest.
 *
 * @author Vítor Brandão <vitor@ppi.io>
 */
class RoutingHelperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    private $params;

    public function setUp()
    {
        $this->params =  array(
            '_controller' => 'BlogModule\Controller\BlogController::showAction',
        );
    }

    public function testParamsAreReturned()
    {
        $k = '_controller';

        // Via constructor
        $routingHelper = new RoutingHelper($this->params);
        $this->assertEquals($this->params[$k], $routingHelper->getParam($k));

        // Via setParams()
        $routingHelper = new RoutingHelper();
        $routingHelper->setParams($this->params);
        $this->assertEquals($this->params[$k], $routingHelper->getParam($k));

        // Via setParam()
        $routingHelper = new RoutingHelper();
        $routingHelper->setParam($k, $this->params[$k]);
        $this->assertEquals($this->params[$k], $routingHelper->getParam($k));
    }

    public function testUnsetParamThrowsInvalidArgumentException()
    {
        $routingHelper = new RoutingHelper();
        $this->setExpectedException('\InvalidArgumentException');
        $routingHelper->getParam('_controller');
    }

    public function testActiveRouteNameIsSaved()
    {
        $routingHelper = new RoutingHelper();
        $routingHelper->setActiveRouteName('test');
        $this->assertEquals('test', $routingHelper->getActiveRouteName());
    }
}
