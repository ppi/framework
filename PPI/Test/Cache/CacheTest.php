<?php
/**
* PPI Cache Unit Tests
*
* @package   Cache
* @author    Paul Dragoonis <dragoonis@php.net>
* @license   http://opensource.org/licenses/mit-license.php MIT
* @link      http://www.ppiframework.com
*/
namespace PPI\Test;
use PPI\Cache;
class CacheTest extends \PHPUnit_Framework_TestCase {

    protected $_cache = null;

    public function setUp()
    {
        $this->_cache = new Cache(array('cache_dir' => TESTPATH . 'Cache/Disk/', 'handler' => 'disk'));
    }

    public function tearDown()
    {
        unset($this->_cache);
    }

    public function testSet()
    {
        $this->_cache->set('foo', 'bar');
        $this->assertEquals('bar', $this->_cache->get('foo'));
        $this->assertNotEquals('bar2', $this->_cache->get('foo', null));
    }

    public function testExists()
    {
        $this->_cache->set('foo', 'bar');
        $this->assertTrue($this->_cache->exists('foo'));
        $this->assertFalse($this->_cache->exists('foo2'));
    }

    public function remove()
    {
        $this->_cache->set('foo', 'bar');
        $this->assertTrue($this->_cache->exists('foo'));
        $this->_cache->remove('foo');
        $this->assertFalse($this->_cache->exists('foo'));
    }
}
