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

		Cookie::$expire   = null;
		Cookie::$path     = null;
		Cookie::$domain   = null;
		Cookie::$secure   = null;
		Cookie::$httponly = null;
	}

	public function testIsCollected() {

		$cookie = new Cookie();
		$this->assertTrue($cookie->isCollected());

		$cookie = new Cookie(array('drink' => 'beer'));
		$this->assertFalse($cookie->isCollected());

		$cookie = new Cookie(array());
		$this->assertFalse($cookie->isCollected());
	}

	public function testCollectCookies() {
		$cookie = new Cookie;
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
		$cookie = new CookieDummy();
		$this->assertEmpty($cookie->setCookies);

		$cookie['foo'] = 'blah';
		$this->assertEquals($cookie->setCookies[0], array(
			'name'     => 'foo',
			'content'  => 'blah',
			'expire'   => null,
			'path'     => null,
			'domain'   => null,
			'secure'   => null,
			'httponly' => null,
		));
	}

	/**
	 * No coverage for setcookie, so use a dummy instead
	 */
	public function testWriteCookieWithChangedSettings() {
		$cookie = new CookieDummy();
		$this->assertEmpty($cookie->setCookies);

		$cookie->setSetting('expire',   10);
		$cookie->setSetting('path',     '/');
		$cookie->setSetting('domain',   '.example.com');
		$cookie->setSetting('secure',   true);
		$cookie->setSetting('httponly', true);

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

	/**
	 * No coverage for setcookie, so use a dummy instead
	 */
	public function testWriteCookieWithGlobalSettings() {
		Cookie::$expire   = 20;
		Cookie::$path     = '/test';
		Cookie::$domain   = '.ppi.io';
		Cookie::$secure   = false;
		Cookie::$httponly = false;

		$cookie = new CookieDummy();
		$this->assertEmpty($cookie->setCookies);

		$cookie['foo'] = 'blah';
		$this->assertEquals($cookie->setCookies[0], array(
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
		Cookie::$expire   = 20;
		Cookie::$path     = '/test';
		Cookie::$domain   = '.ppi.io';
		Cookie::$secure   = false;
		Cookie::$httponly = false;

		$cookie = new CookieDummy();
		$this->assertEmpty($cookie->setCookies);

		$cookie->setSetting('expire',   10);
		$cookie->setSetting('path',     '/');
		$cookie->setSetting('domain',   '.example.com');
		$cookie->setSetting('secure',   true);
		// intentional missing httponly param

		$cookie['foo'] = 'blah';
		$this->assertEquals($cookie->setCookies[0], array(
			'name'     => 'foo',
			'content'  => 'blah',
			'expire'   => 10,
			'path'     => '/',
			'domain'   => '.example.com',
			'secure'   => true,
			'httponly' => false,
		));
	}
}

class CookieDummy extends Cookie {
	public $setCookies = array();

	protected function _setCookie($name, $content, $expire, $path, $domain, $secure, $httponly) {
		$this->setCookies[] = array(
			'name'     => $name,
			'content'  => $content,
			'expire'   => $expire,
			'path'     => $path,
			'domain'   => $domain,
			'secure'   => $secure,
			'httponly' => $httponly,
		);
	}
}
