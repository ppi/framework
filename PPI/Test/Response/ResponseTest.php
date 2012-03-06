<?php
/**
* Unit test for PPI Response
*
* @package   Core
* @author    Paul Dragoonis <dragoonis@php.net>
* @license   http://opensource.org/licenses/mit-license.php MIT
* @link      http://www.ppiframework.com
*/
namespace PPI\Test\Response;
use PPI\Response;
class ResponseTest extends \PHPUnit_Framework_TestCase {

	protected $_response = null;

	public function setUp() {
		$this->_response = new Response(array(
			'cssFiles' => array('foo', 'bar'),
			'jsFiles'  => array('foo', 'bar'),
			'charset'  => 'utf-8',
			'flash'    => array('mode' => 'failure', 'message' => 'There has been a failure')
		));
	}

	public function tearDown() {
		unset($this->_response);
	}

	public function testSetCSS() {

		$this->_response->addCSS('baz');
		$cssFiles = $this->_response->getCSSFiles();
		$this->assertEquals('foo', $cssFiles[0]);
		$this->assertEquals('bar', $cssFiles[1]);
		$this->assertEquals('baz', $cssFiles[2]);
		$this->_response->clearCSS();
	}

	public function testClearCSS() {

		$this->_response->addCSS('baz');
		$this->_response->clearCSS();
		$cssFiles = $this->_response->getCSSFiles();
		$this->assertTrue(empty($cssFiles));
	}

	public function testSetJS() {

		$this->_response->addJS('baz');
		$jsFiles = $this->_response->getJSFiles();
		$this->assertEquals('foo', $jsFiles[0]);
		$this->assertEquals('bar', $jsFiles[1]);
		$this->assertEquals('baz', $jsFiles[2]);
		$this->_response->clearJS();
	}

	public function testClearJS() {

		$this->_response->addJS('foo');
		$this->_response->clearJS();
		$jsFiles = $this->_response->getJSFiles();
		$this->assertTrue(empty($jsFiles));
	}

	public function testCharsetGet() {
		$this->assertEquals('utf-8', $this->_response->getCharset());
	}

	public function testCharsetSet() {

		$this->_response->setCharset('foo');
		$this->assertEquals('foo', $this->_response->getCharset());
	}

	public function testFlashGet() {

		$flash = $this->_response->getFlash();
		$this->assertTrue(isset($flash['mode'], $flash['message']));
		$this->assertEquals('failure', $flash['mode']);
		$this->assertEquals('There has been a failure', $flash['message']);
	}

	public function testFlashSet() {

		$this->_response->setFlash('New Message', true);
		$flash = $this->_response->getFlash();
		$this->assertTrue(isset($flash['mode'], $flash['message']));
		$this->assertEquals('success', $flash['mode']);
		$this->assertEquals('New Message', $flash['message']);
	}

	public function testFlashClear() {

		$this->_response->setFlash('New Message', true);
		$this->_response->clearFlash();
		$flash = $this->_response->getFlash();
		$this->assertTrue(empty($flash));

	}

}

