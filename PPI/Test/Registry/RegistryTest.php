<?php
/**
* Unit test for the PPI Registry
*
* @package   Core
* @author    Paul Dragoonis <dragoonis@php.net>
* @license   http://opensource.org/licenses/mit-license.php MIT
* @link      http://www.ppiframework.com
*/
namespace PPI\Test\Registry;
use PPI\Registry;
class RegistryTest extends \PHPUnit_Framework_TestCase {

    protected $_reg = null;

    public function setUp() {}

    public function tearDown() {}

    public function testRemove()
    {
        Registry::set('foo', 'foo');
        Registry::remove('foo');
        $this->assertFalse(Registry::exists('foo'));
    }

    public function testSet()
    {
        Registry::set('foo', 'foo');
        $this->assertEquals('foo', Registry::get('foo'));
        Registry::remove('foo');
    }

    public function testIsset()
    {
        Registry::set('foo2', 'foo');
        $this->assertFalse(Registry::exists('foo'));
        $this->assertTrue(Registry::exists('foo2'));
        Registry::remove('foo2');
    }
}
