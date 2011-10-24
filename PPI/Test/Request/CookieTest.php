<?php
/**
 * Unit test for the PPI Request Cookie
 *
 * @package   Core
 * @author    Paul Dragoonis <dragoonis@php.net>
 * @license   http://opensource.org/licenses/mit-license.php MIT
 * @link      http://www.ppiframework.com
*/
namespace PPI\Test\Request;
use PPI\Request\Cookie;
class CookieTest extends \PHPUnit_Framework_TestCase {
	public function setUp() {
	}

	public function tearDown() {
	}

	public function testIsCollected() {

		$cookie = new Cookie();
		$this->assertTrue($cookie->isCollected());

		$cookie = new Cookie(array('drink' => 'beer'));
		$this->assertFalse($cookie->isCollected());

		$cookie = new Cookie(array());
		$this->assertFalse($cookie->isCollected());
	}

	public function testCustomGet() {
		$cookie = new Cookie(array('drink' => 'beer'));
		$this->assertEquals('beer', $cookie['drink']);
		$this->assertEquals(null,   $cookie['foo']);
		$this->assertEquals(null,   $cookie['random']);
		$this->assertFalse($cookie->isCollected());
	}
}
