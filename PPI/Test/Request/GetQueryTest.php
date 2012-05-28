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
use PPI\Request\Get;
class GetQueryTest extends \PHPUnit_Framework_TestCase {
    public function setUp()
    {
        $_GET = array('foo' => 'bar', 'bar' => 'foo');
    }

    public function tearDown()
    {
        $_GET = array();
    }

    public function testIsCollected()
    {
        $get = new Get();
        $this->assertTrue($get->isCollected());

        $get = new Get(array('drink' => 'beer'));
        $this->assertFalse($get->isCollected());

        $get = new Get(array());
        $this->assertFalse($get->isCollected());
    }

    public function testCollectGet()
    {
        $get = new Get;
        $this->assertEquals('foo', $get['bar']);
        $this->assertEquals('bar', $get['foo']);
        $this->assertEquals(null,  $get['random']);
        $this->assertTrue($get->isCollected());
    }

    public function testCustomGet()
    {
        $get = new Get(array('drink' => 'beer'));
        $this->assertEquals('beer', $get['drink']);
        $this->assertEquals(null,   $get['foo']);
        $this->assertEquals(null,   $get['random']);
        $this->assertFalse($get->isCollected());
    }

    public function testGetString()
    {
        $get = new Get('first=value&arr[]=foo+bar&arr[]=baz');
        $this->assertEquals('value',   $get['first']);
        $this->assertEquals('foo bar', $get['arr'][0]);
        $this->assertEquals('baz',     $get['arr'][1]);
    }
}
