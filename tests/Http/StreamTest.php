<?php
/**
 * This file is part of the PPI Framework.
 *
 * @copyright  Copyright (c) 2011-2015 Paul Dragoonis <paul@ppi.io>
 * @license    http://opensource.org/licenses/mit-license.php MIT
 *
 * @link       http://www.ppi.io
 */

namespace PPI\FrameworkTest\Http;

use ReflectionProperty;
use PPI\Framework\Http\Stream;

/**
 * Class StreamTest.
 */
class StreamTest extends \PHPUnit_Framework_TestCase
{
    public $tmpnam;

    public function setUp()
    {
        $this->tmpnam = null;
        $this->stream = new Stream('php://memory', 'wb+');
    }

    public function tearDown()
    {
        if ($this->tmpnam && file_exists($this->tmpnam)) {
            unlink($this->tmpnam);
        }
    }

    /**
     * @group http
     */
    public function testCanInstantiateWithStreamIdentifier()
    {
        $this->assertInstanceOf('PPI\Framework\Http\Stream', $this->stream);
    }

    /**
     * @group http
     */
    public function testCanInstantiteWithStreamResource()
    {
        $resource = fopen('php://memory', 'wb+');
        $stream   = new Stream($resource);
        $this->assertInstanceOf('PPI\Framework\Http\Stream', $stream);
    }

    /**
     * @group http
     */
    public function testIsReadableReturnsFalseIfStreamIsNotReadable()
    {
        $this->tmpnam = tempnam(sys_get_temp_dir(), 'phly');
        $stream       = new Stream($this->tmpnam, 'w');
        $this->assertFalse($stream->isReadable());
    }

    /**
     * @group http
     */
    public function testIsWritableReturnsFalseIfStreamIsNotWritable()
    {
        $stream = new Stream('php://memory', 'r');
        $this->assertFalse($stream->isWritable());
    }

    /**
     * @group http
     */
    public function testToStringRetrievesFullContentsOfStream()
    {
        $message = 'foo bar';
        $this->stream->write($message);
        $this->assertEquals($message, (string) $this->stream);
    }

    /**
     * @group http
     */
    public function testDetachReturnsResource()
    {
        $resource = fopen('php://memory', 'wb+');
        $stream   = new Stream($resource);
        $this->assertSame($resource, $stream->detach());
    }

    /**
     * @group http
     */
    public function testPassingInvalidStreamResourceToConstructorRaisesException()
    {
        $this->setExpectedException('InvalidArgumentException');
        $stream = new Stream(array('  THIS WILL NOT WORK  '));
    }

    /**
     * @group http
     */
    public function testStringSerializationReturnsEmptyStringWhenStreamIsNotReadable()
    {
        $this->tmpnam = tempnam(sys_get_temp_dir(), 'phly');
        file_put_contents($this->tmpnam, 'FOO BAR');
        $stream = new Stream($this->tmpnam, 'w');

        $this->assertEquals('', $stream->__toString());
    }

    /**
     * @group http
     */
    public function testCloseClosesResource()
    {
        $this->tmpnam = tempnam(sys_get_temp_dir(), 'phly');
        $resource     = fopen($this->tmpnam, 'wb+');
        $stream       = new Stream($resource);
        $stream->close();
        $this->assertFalse(is_resource($resource));
    }

    /**
     * @group http
     */
    public function testCloseUnsetsResource()
    {
        $this->tmpnam = tempnam(sys_get_temp_dir(), 'phly');
        $resource     = fopen($this->tmpnam, 'wb+');
        $stream       = new Stream($resource);
        $stream->close();

        $this->assertNull($stream->detach());
    }

    /**
     * @group http
     */
    public function testCloseDoesNothingAfterDetach()
    {
        $this->tmpnam = tempnam(sys_get_temp_dir(), 'phly');
        $resource     = fopen($this->tmpnam, 'wb+');
        $stream       = new Stream($resource);
        $detached     = $stream->detach();

        $stream->close();
        $this->assertTrue(is_resource($detached));
        $this->assertSame($resource, $detached);
    }

    /**
     * @group http
     */
    public function testSizeReportsNullWhenNoResourcePresent()
    {
        $this->stream->detach();
        $this->assertNull($this->stream->getSize());
    }

    /**
     * @group http
     */
    public function testTellReportsCurrentPositionInResource()
    {
        $this->tmpnam = tempnam(sys_get_temp_dir(), 'phly');
        file_put_contents($this->tmpnam, 'FOO BAR');
        $resource = fopen($this->tmpnam, 'wb+');
        $stream   = new Stream($resource);

        fseek($resource, 2);

        $this->assertEquals(2, $stream->tell());
    }

    /**
     * @group http
     */
    public function testTellReturnsFalseIfResourceIsDetached()
    {
        $this->tmpnam = tempnam(sys_get_temp_dir(), 'phly');
        file_put_contents($this->tmpnam, 'FOO BAR');
        $resource = fopen($this->tmpnam, 'wb+');
        $stream   = new Stream($resource);

        fseek($resource, 2);
        $stream->detach();
        $this->assertFalse($stream->tell());
    }

    /**
     * @group http
     */
    public function testEofReportsFalseWhenNotAtEndOfStream()
    {
        $this->tmpnam = tempnam(sys_get_temp_dir(), 'phly');
        file_put_contents($this->tmpnam, 'FOO BAR');
        $resource = fopen($this->tmpnam, 'wb+');
        $stream   = new Stream($resource);

        fseek($resource, 2);
        $this->assertFalse($stream->eof());
    }

    /**
     * @group http
     */
    public function testEofReportsTrueWhenAtEndOfStream()
    {
        $this->tmpnam = tempnam(sys_get_temp_dir(), 'phly');
        file_put_contents($this->tmpnam, 'FOO BAR');
        $resource = fopen($this->tmpnam, 'wb+');
        $stream   = new Stream($resource);

        while (! feof($resource)) {
            fread($resource, 4096);
        }
        $this->assertTrue($stream->eof());
    }

    /**
     * @group http
     */
    public function testEofReportsTrueWhenStreamIsDetached()
    {
        $this->tmpnam = tempnam(sys_get_temp_dir(), 'phly');
        file_put_contents($this->tmpnam, 'FOO BAR');
        $resource = fopen($this->tmpnam, 'wb+');
        $stream   = new Stream($resource);

        fseek($resource, 2);
        $stream->detach();
        $this->assertTrue($stream->eof());
    }

    /**
     * @group http
     */
    public function testIsSeekableReturnsTrueForReadableStreams()
    {
        $this->tmpnam = tempnam(sys_get_temp_dir(), 'phly');
        file_put_contents($this->tmpnam, 'FOO BAR');
        $resource = fopen($this->tmpnam, 'wb+');
        $stream   = new Stream($resource);
        $this->assertTrue($stream->isSeekable());
    }

    /**
     * @group http
     */
    public function testIsSeekableReturnsFalseForDetachedStreams()
    {
        $this->tmpnam = tempnam(sys_get_temp_dir(), 'phly');
        file_put_contents($this->tmpnam, 'FOO BAR');
        $resource = fopen($this->tmpnam, 'wb+');
        $stream   = new Stream($resource);
        $stream->detach();
        $this->assertFalse($stream->isSeekable());
    }

    /**
     * @group http
     */
    public function testSeekAdvancesToGivenOffsetOfStream()
    {
        $this->tmpnam = tempnam(sys_get_temp_dir(), 'phly');
        file_put_contents($this->tmpnam, 'FOO BAR');
        $resource = fopen($this->tmpnam, 'wb+');
        $stream   = new Stream($resource);
        $this->assertTrue($stream->seek(2));
        $this->assertEquals(2, $stream->tell());
    }

    /**
     * @group http
     */
    public function testRewindResetsToStartOfStream()
    {
        $this->tmpnam = tempnam(sys_get_temp_dir(), 'phly');
        file_put_contents($this->tmpnam, 'FOO BAR');
        $resource = fopen($this->tmpnam, 'wb+');
        $stream   = new Stream($resource);
        $this->assertTrue($stream->seek(2));
        $stream->rewind();
        $this->assertEquals(0, $stream->tell());
    }

    /**
     * @group http
     */
    public function testSeekReturnsFalseWhenStreamIsDetached()
    {
        $this->tmpnam = tempnam(sys_get_temp_dir(), 'phly');
        file_put_contents($this->tmpnam, 'FOO BAR');
        $resource = fopen($this->tmpnam, 'wb+');
        $stream   = new Stream($resource);
        $stream->detach();
        $this->assertFalse($stream->seek(2));
        $this->assertEquals(0, ftell($resource));
    }

    /**
     * @group http
     */
    public function testIsWritableReturnsFalseWhenStreamIsDetached()
    {
        $this->tmpnam = tempnam(sys_get_temp_dir(), 'phly');
        file_put_contents($this->tmpnam, 'FOO BAR');
        $resource = fopen($this->tmpnam, 'wb+');
        $stream   = new Stream($resource);
        $stream->detach();
        $this->assertFalse($stream->isWritable());
    }

    /**
     * @group http
     */
    public function testWriteReturnsFalseWhenStreamIsDetached()
    {
        $this->tmpnam = tempnam(sys_get_temp_dir(), 'phly');
        file_put_contents($this->tmpnam, 'FOO BAR');
        $resource = fopen($this->tmpnam, 'wb+');
        $stream   = new Stream($resource);
        $stream->detach();
        $this->assertFalse($stream->write('bar'));
    }

    /**
     * @group http
     */
    public function testIsReadableReturnsFalseWhenStreamIsDetached()
    {
        $this->tmpnam = tempnam(sys_get_temp_dir(), 'phly');
        file_put_contents($this->tmpnam, 'FOO BAR');
        $resource = fopen($this->tmpnam, 'wb+');
        $stream   = new Stream($resource);
        $stream->detach();
        $this->assertFalse($stream->isReadable());
    }

    /**
     * @group http
     */
    public function testReadReturnsEmptyStringWhenStreamIsDetached()
    {
        $this->tmpnam = tempnam(sys_get_temp_dir(), 'phly');
        file_put_contents($this->tmpnam, 'FOO BAR');
        $resource = fopen($this->tmpnam, 'r');
        $stream   = new Stream($resource);
        $stream->detach();
        $this->assertEquals('', $stream->read(4096));
    }

    /**
     * @group http
     */
    public function testReadReturnsEmptyStringWhenAtEndOfFile()
    {
        $this->tmpnam = tempnam(sys_get_temp_dir(), 'phly');
        file_put_contents($this->tmpnam, 'FOO BAR');
        $resource = fopen($this->tmpnam, 'r');
        $stream   = new Stream($resource);
        while (! feof($resource)) {
            fread($resource, 4096);
        }
        $this->assertEquals('', $stream->read(4096));
    }

    /**
     * @group http
     */
    public function testGetContentsReturnsEmptyStringIfStreamIsNotReadable()
    {
        $this->tmpnam = tempnam(sys_get_temp_dir(), 'phly');
        file_put_contents($this->tmpnam, 'FOO BAR');
        $resource = fopen($this->tmpnam, 'w');
        $stream   = new Stream($resource);
        $this->assertEquals('', $stream->getContents());
    }

    /**
     * @return array
     */
    public function invalidResources()
    {
        $this->tmpnam = tempnam(sys_get_temp_dir(), 'PHLY');

        return array(
            'null'                => array( null ),
            'false'               => array( false ),
            'true'                => array( true ),
            'int'                 => array( 1 ),
            'float'               => array( 1.1 ),
            'string-non-resource' => array( 'foo-bar-baz' ),
            'array'               => array( array( fopen($this->tmpnam, 'r+') ) ),
            'object'              => array( (object) array( 'resource' => fopen($this->tmpnam, 'r+') ) ),
        );
    }

    /**
     * @group http
     * @dataProvider invalidResources
     */
    public function testAttachWithNonStringNonResourceRaisesException($resource)
    {
        $this->setExpectedException('InvalidArgumentException', 'Invalid stream');
        $this->stream->attach($resource);
    }

    /**
     * @group http
     */
    public function testAttachWithResourceAttachesResource()
    {
        $this->tmpnam = tempnam(sys_get_temp_dir(), 'PHLY');
        $resource     = fopen($this->tmpnam, 'r+');
        $this->stream->attach($resource);

        $r = new ReflectionProperty($this->stream, 'resource');
        $r->setAccessible(true);
        $test = $r->getValue($this->stream);
        $this->assertSame($resource, $test);
    }

    /**
     * @group http
     */
    public function testAttachWithStringRepresentingResourceCreatesAndAttachesResource()
    {
        $this->tmpnam = tempnam(sys_get_temp_dir(), 'PHLY');
        $this->stream->attach($this->tmpnam);

        $resource = fopen($this->tmpnam, 'r+');
        fwrite($resource, 'FooBar');

        $this->stream->rewind();
        $test = (string) $this->stream;
        $this->assertEquals('FooBar', $test);
    }

    /**
     * @group http
     */
    public function testGetContentsShouldGetFullStreamContents()
    {
        $this->tmpnam = tempnam(sys_get_temp_dir(), 'PHLY');
        $resource     = fopen($this->tmpnam, 'r+');
        $this->stream->attach($resource);

        fwrite($resource, 'FooBar');

        // rewind, because current pointer is at end of stream!
        $this->stream->rewind();
        $test = $this->stream->getContents();
        $this->assertEquals('FooBar', $test);
    }

    /**
     * @group http
     */
    public function testGetContentsShouldReturnStreamContentsFromCurrentPointer()
    {
        $this->tmpnam = tempnam(sys_get_temp_dir(), 'PHLY');
        $resource     = fopen($this->tmpnam, 'r+');
        $this->stream->attach($resource);

        fwrite($resource, 'FooBar');

        // seek to position 3
        $this->stream->seek(3);
        $test = $this->stream->getContents();
        $this->assertEquals('Bar', $test);
    }

    /**
     * @group http
     */
    public function testGetMetadataReturnsAllMetadataWhenNoKeyPresent()
    {
        $this->tmpnam = tempnam(sys_get_temp_dir(), 'PHLY');
        $resource     = fopen($this->tmpnam, 'r+');
        $this->stream->attach($resource);

        $expected = stream_get_meta_data($resource);
        $test     = $this->stream->getMetadata();

        $this->assertEquals($expected, $test);
    }

    /**
     * @group http
     */
    public function testGetMetadataReturnsDataForSpecifiedKey()
    {
        $this->tmpnam = tempnam(sys_get_temp_dir(), 'PHLY');
        $resource     = fopen($this->tmpnam, 'r+');
        $this->stream->attach($resource);

        $metadata = stream_get_meta_data($resource);
        $expected = $metadata['uri'];

        $test     = $this->stream->getMetadata('uri');

        $this->assertEquals($expected, $test);
    }

    /**
     * @group http
     */
    public function testGetMetadataReturnsNullIfNoDataExistsForKey()
    {
        $this->tmpnam = tempnam(sys_get_temp_dir(), 'PHLY');
        $resource     = fopen($this->tmpnam, 'r+');
        $this->stream->attach($resource);

        $this->assertNull($this->stream->getMetadata('TOTALLY_MADE_UP'));
    }

    /**
     * @group http
     */
    public function testGetSizeReturnsStreamSize()
    {
        $resource = fopen(__FILE__, 'r');
        $expected = fstat($resource);
        $stream   = new Stream($resource);
        $this->assertEquals($expected['size'], $stream->getSize());
    }
}
