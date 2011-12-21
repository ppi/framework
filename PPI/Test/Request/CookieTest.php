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
		$_COOKIE = array('foo' => 'bar', 'bar' => 'foo');
	}

	public function tearDown() {
		$_COOKIE = array();
	}

	public function testIsCollected() {

		$cookie = new Cookie();
		$this->assertTrue($cookie->isCollected());

		$cookie = new Cookie(array('drink' => 'beer'));
		$this->assertFalse($cookie->isCollected());

		$cookie = new Cookie(array());
		$this->assertTrue($cookie->isCollected());
	}

	public function testCollectCookies() {
		$cookie = new Cookie();
		$this->assertEquals('foo', $cookie['bar']);
		$this->assertEquals('bar', $cookie['foo']);
		$this->assertEquals(null,  $cookie['random']);
		$this->assertTrue($cookie->isCollected());
	}

	public function testCustomCookie() {
		$cookie = new Cookie(array('drink' => 'beer'));
		$this->assertEquals('beer', $cookie['drink']);
		$this->assertEquals(null,   $cookie['foo']);
		$this->assertEquals(null,   $cookie['random']);
		$this->assertFalse($cookie->isCollected());
	}

	/**
	 * No coverage for setcookie, so use a dummy instead
	 */
	public function testWriteCookie() {

		$cookie['foo'] = 'blah';
		
		$this->assertEquals('blah', $cookie['foo']);
	}

	/**
	 * No coverage for setcookie, so use a dummy instead
	 */
	public function testWriteCookieWithChangedSettings() {
		$cookie = new Cookie(array('foo' => 'bar'));
		unset($cookie['foo']);
		$this->assertEmpty($cookie->all());

		$cookie->setSetting('expire',   10);
		$cookie->setSetting('path',     '/');
		$cookie->setSetting('domain',   '.example.com');
		$cookie->setSetting('secure',   true);
		$cookie->setSetting('httponly', true);

		$cookie['foo'] = 'blah';
		$this->assertEquals($cookie->getCookie('foo'), array(
			'name'     => 'foo',
			'content'  => 'blah',
			'expire'   => 10,
			'path'     => '/',
			'domain'   => '.example.com',
			'secure'   => true,
			'httponly' => true,
		));
	}

	/**
	 * No coverage for setcookie, so use a dummy instead
	 */
	public function testWriteCookieWithGlobalSettings() {

		$cookie = new Cookie(array('foo' => 'foo_temp'));
		$cookie->setSetting('expire',   20);
		$cookie->setSetting('path',     '/test');
		$cookie->setSetting('domain',   '.ppi.io');
		$cookie->setSetting('secure',   false);
		$cookie->setSetting('httponly', false);

		$cookie['foo'] = 'blah';
		$this->assertEquals($cookie->getCookie('foo'), array(
			'name'     => 'foo',
			'content'  => 'blah',
			'expire'   => 20,
			'path'     => '/test',
			'domain'   => '.ppi.io',
			'secure'   => false,
			'httponly' => false,
		));
	}

	/**
	 * No coverage for setcookie, so use a dummy instead
	 */
	public function testWriteCookieWithGlobalAndChangedSettings() {

		$cookie = new Cookie(array('foo' => 'bar'));

		$cookie->setSetting('expire',   10);
		$cookie->setSetting('path',     '/test');
		$cookie->setSetting('domain',   '.ppi.io');
		$cookie->setSetting('secure',   true);
		$cookie->setSetting('httponly', false);

		// intentional missing httponly param
		$cookie['foo'] = 'blah';
		$this->assertEquals($cookie->getCookie('foo'), array(
			'name'     => 'foo',
			'content'  => 'blah',
			'expire'   => 10,
			'path'     => '/test',
			'domain'   => '.ppi.io',
			'secure'   => true,
			'httponly' => false,
		));
	}

	/**
	 * No coverage for setcookie, so use a dummy instead
	 */
	public function testWriteCookieWithArraySettings() {
		$cookie = new CookieDummy();
		$this->assertEmpty($cookie->setCookies);

		$cookie->setSetting(array(
			'expire'   => 10,
			'path'     => '/',
			'domain'   => '.example.com',
			'secure'   => true,
			'httponly' => true,
		));

		$cookie['foo'] = 'blah';
		$this->assertEquals($cookie->setCookies[0], array(
			'name'     => 'foo',
			'content'  => 'blah',
			'expire'   => 10,
			'path'     => '/',
			'domain'   => '.example.com',
			'secure'   => true,
			'httponly' => true,
		));
	}

}
