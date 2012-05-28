<?php
/**
 * Unit test for the PPI Request Post
 *
 * @package   Core
 * @author    Paul Dragoonis <dragoonis@php.net>
 * @license   http://opensource.org/licenses/mit-license.php MIT
 * @link      http://www.ppiframework.com
*/
namespace PPI\Test\Request;
use PPI\Request\Post;
class PostTest extends \PHPUnit_Framework_TestCase {
    public function setUp()
    {
        $_POST = array('foo' => 'bar', 'bar' => 'foo');
    }

    public function tearDown()
    {
        $_POST = array();
    }

    public function testIsCollected()
    {
        $post = new Post;
        $this->assertTrue($post->isCollected());

        $post = new Post(array('drink' => 'beer'));
        $this->assertFalse($post->isCollected());

        $post = new Post(array());
        $this->assertTrue($post->isCollected());
    }

    public function testCollectPost()
    {
        $post = new Post;
        $this->assertEquals('foo', $post['bar']);
        $this->assertEquals('bar', $post['foo']);
        $this->assertEquals(null,  $post['random']);
        $this->assertTrue($post->isCollected());
    }

    public function testCustomPost()
    {
        $post = new Post(array('drink' => 'beer'));
        $this->assertEquals('beer', $post['drink']);
        $this->assertEquals(null,   $post['foo']);
        $this->assertEquals(null,   $post['random']);
        $this->assertFalse($post->isCollected());
    }
}
