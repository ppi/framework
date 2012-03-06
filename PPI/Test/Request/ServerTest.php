<?php
/**
 * Unit test for the PPI Request Server
 *
 * @package   Core
 * @author    Paul Dragoonis <dragoonis@php.net>
 * @license   http://opensource.org/licenses/mit-license.php MIT
 * @link      http://www.ppiframework.com
*/
namespace PPI\Test\Request;
use PPI\Request\Server;
class ServerTest extends \PHPUnit_Framework_TestCase {
	public function setUp() {
		$_SERVER = array('foo' => 'bar', 'bar' => 'foo');
	}

	public function tearDown() {
		$_SERVER = array();
	}

	public function testIsCollected() {
		$server = new Server;
		$this->assertTrue($server->isCollected());

		$server = new Server(array('drink' => 'beer'));
		$this->assertFalse($server->isCollected());

		$server = new Server(array());
		$this->assertFalse($server->isCollected());
	}

	public function testCollectServer() {
		$server = new Server;
		$this->assertEquals('foo', $server['bar']);
		$this->assertEquals('bar', $server['foo']);
		$this->assertEquals(null,  $server['random']);
		$this->assertTrue($server->isCollected());
	}

	public function testCustomServer() {
		$server = new Server(array('drink' => 'beer'));
		$this->assertEquals('beer', $server['drink']);
		$this->assertEquals(null,   $server['foo']);
		$this->assertEquals(null,   $server['random']);
		$this->assertFalse($server->isCollected());
	}
}