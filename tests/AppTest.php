<?php
/**
 * This file is part of the PPI Framework.
 *
 * @copyright  Copyright (c) 2011-2014 Paul Dragoonis <paul@ppi.io>
 * @license    http://opensource.org/licenses/mit-license.php MIT
 * @link       http://www.ppi.io
 */

namespace PPI;

use PPI\App;

/**
 * Class AppTest
 * @package PPI
 *
 * @author Vítor Brandão <vitor@ppi.io>
 */
class AppTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $env = 'test_env';
        $debug = true;
        $app = new App(array(
            'environment'   => $env,
            'debug'         => $debug
        ));

        $this->assertEquals($env, $app->getEnvironment());
        $this->assertEquals($debug, $app->isDebug());
        $this->assertLessThanOrEqual(microtime(true), $app->getStartTime());
    }
}