<?php
/**
 * This file is part of the PPI Framework.
 *
 * @copyright  Copyright (c) 2011-2015 Paul Dragoonis <paul@ppi.io>
 * @license    http://opensource.org/licenses/mit-license.php MIT
 *
 * @link       http://www.ppi.io
 */

namespace PPI;

use PPI\Framework\Tests\Fixtures\AppForTest;

/**
 * Class AppTest.
 *
 * @author Vítor Brandão <vitor@ppi.io>
 */
class AppTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $env     = 'test_env';
        $debug   = true;
        $rootDir = __DIR__;
        $name    = 'testName';
        $app     = new AppForTest(array(
            'environment'   => $env,
            'debug'         => $debug,
            'rootDir'       => $rootDir,
            'name'          => $name,
        ));

        $this->assertEquals($env, $app->getEnvironment());
        $this->assertEquals($debug, $app->isDebug());
        $this->assertEquals($rootDir, $app->getRootDir());
        $this->assertEquals($name, $app->getName());
        $this->assertFalse($app->isBooted());
        $this->assertLessThanOrEqual(microtime(true), $app->getStartTime());
        $this->assertNull($app->getServiceManager());
        $this->assertNull($app->getContainer());
    }

    public function testClone()
    {
        $env   = 'test_env';
        $debug = true;
        $app   = new AppForTest(array('environment' => $env, 'debug' => $debug));

        $clone = clone $app;

        $this->assertEquals($env, $clone->getEnvironment());
        $this->assertEquals($debug, $clone->isDebug());
        $this->assertFalse($clone->isBooted());
        $this->assertLessThanOrEqual(microtime(true), $clone->getStartTime());
        $this->assertNull($clone->getContainer());
    }

    public function testGetRootDir()
    {
        $app = new AppForTest(array(
            'environment'   => 'test',
            'debug'         => true,
            'rootDir'       => __DIR__,
        ));

        $this->assertEquals(__DIR__, realpath($app->getRootDir()));

        chdir(__DIR__);
        $app = new AppForTest(array('environment' => 'test', 'debug' => true));
        $this->assertEquals(__DIR__, realpath($app->getRootDir()));
    }

    public function testGetName()
    {
        $app = new AppForTest(array(
            'environment'   => 'test',
            'debug'         => true,
            'rootDir'       => __DIR__,
        ));
        $this->assertEquals(basename(__DIR__), $app->getName());

        $app = new AppForTest(array(
            'environment'   => 'test',
            'debug'         => true,
            'rootDir'       => __DIR__,
            'name'          => 'testName',
        ));
        $this->assertEquals('testName', $app->getName());
    }
}
