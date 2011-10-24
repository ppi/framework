<?php
/**
* Unit test for PPI Session
*
* @package   Core
* @author    Paul Dragoonis <dragoonis@php.net>
* @license   http://opensource.org/licenses/mit-license.php MIT
* @link      http://www.ppiframework.com
*/
namespace PPI\Test\Session;
use PPI\Session;
class SessionTest extends \PHPUnit_Framework_TestCase {

	protected $_session = null;

	public function setUp() {
		$this->_session = new Session(array('data' => array('foo' => 'bar')));
	}

	public function tearDown() {
		unset($this->_session);
	}

	public function testSessionGet() {
		$this->assertEquals('bar', $this->_session->get('foo'));
	}

	public function testSessionSet() {

		$this->_session->set('foo2', 'bar2');
		$this->assertEquals('bar2', $this->_session->get('foo2'));
	}

	public function testSessionExists() {

		$this->assertTrue($this->_session->exists('foo'));
		$this->assertFalse($this->_session->exists('foofoo'));
	}

	public function testSessionRemove() {

		$this->_session->remove('foo');
		$this->assertFalse($this->_session->exists('foo'));
	}

	public function testSessionRemoveAll() {

		$this->_session->set('foo', 'bar');
		$this->_session->removeAll();
		$this->assertFalse($this->_session->exists('foo') && $this->_session->exists('foo2'));

	}
}