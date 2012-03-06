<?php
/**
 * Unit test for the PPI Request GetQuery
 *
 * @package   Core
 * @author    Paul Dragoonis <dragoonis@php.net>
 * @license   http://opensource.org/licenses/mit-license.php MIT
 * @link      http://www.ppiframework.com
*/
namespace PPI\Test\Request;
use PPI\Request\Session;
class SessionTest extends \PHPUnit_Framework_TestCase {
	public function setUp() {
		$_SESSION = array('foo' => 'bar', 'drink' => 'beer');
	}

	public function tearDown() {
		$_SESSION = null;
	}

	public function testIsCollected() {
		$session = new Session();
		$this->assertTrue($session->isCollected());

		$session = new Session(array('drink' => 'beer'));
		$this->assertFalse($session->isCollected());

		$session = new Session(array());
		$this->assertFalse($session->isCollected());
	}

	public function testCollectSession() {
		$session = new Session;
		$this->assertEquals('bar',  $session['foo']);
		$this->assertEquals(null,   $session['bar']);
		$this->assertEquals('beer', $session['drink']);
		$this->assertTrue($session->isCollected());
	}

	public function testCustomSession() {
		$session = new Session(array('drink' => 'beer'));
		$this->assertEquals('beer', $session['drink']);
		$this->assertEquals(null,   $session['foo']);
		$this->assertEquals(null,   $session['random']);
		$this->assertFalse($session->isCollected());
	}

	public function testAddSession() {
		$session = new Session;
		$this->assertEquals(null, $session['test']);
		$this->assertFalse(isset($_SESSION['test']));

		$session['test'] = 'foo';
		$this->assertEquals('foo', $session['test']);
		$this->assertEquals('foo', $_SESSION['test']);
	}

	public function testchangeSession() {
		$session = new Session;

		$session['drink'] = 'wiskey';
		$this->assertEquals('wiskey', $session['drink']);
		$this->assertEquals('wiskey', $_SESSION['drink']);
	}

	public function testRemoveSession() {
		$session = new Session;

		unset($session['drink']);
		$this->assertNull($session['drink']);
		$this->assertFalse(isset($_SESSION['drink']));
	}

	public function testRemoveSessionBySettingNull() {
		$session = new Session;

		$session['drink'] = null;
		$this->assertNull($session['drink']);
		$this->assertFalse(isset($_SESSION['drink']));
	}
}
