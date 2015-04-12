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

use Phly\Http\Stream;
use PPI\Framework\Http\Response;

/**
 * Class ResponseTest.
 */
class ResponseTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->response = new Response();
    }

    /**
     * @group http
     */
    public function testImplementsPsr7ResponseInterface()
    {
        $r = new \ReflectionObject($this->response);
        $this->assertTrue($r->implementsInterface('Psr\Http\Message\ResponseInterface'));
    }

    /**
     * @group http
     */
    public function testStatusCodeIs200ByDefault()
    {
        $this->assertEquals(200, $this->response->getStatusCode());
    }

    /**
     * @group http
     */
    public function testStatusCodeMutatorReturnsCloneWithChanges()
    {
        $response = $this->response->withStatus(400);
        $this->assertNotSame($this->response, $response);
        $this->assertEquals(400, $response->getStatusCode());
    }

    /**
     * @return array
     */
    public function invalidStatusCodes()
    {
        return array(
            'too-low'  => array(99),
            'too-high' => array(600),
            'null'     => array(null),
            'bool'     => array(true),
            'string'   => array('foo'),
            'array'    => array(array(200)),
            'object'   => array((object) (200)),
        );
    }

    /**
     * @group        http
     * @dataProvider invalidStatusCodes
     */
    public function testCannotSetInvalidStatusCode($code)
    {
        $this->setExpectedException('InvalidArgumentException');
        $this->response->withStatus($code);
    }

    /**
     * @group http
     */
    public function testReasonPhraseDefaultsToStandards()
    {
        $response = $this->response->withStatus(422);
        $this->assertEquals('Unprocessable Entity', $response->getReasonPhrase());
    }

    /**
     * @group http
     */
    public function testCanSetCustomReasonPhrase()
    {
        $response = $this->response->withStatus(422, 'Foo Bar!');
        $this->assertEquals('Foo Bar!', $response->getReasonPhrase());
    }

    /**
     * @group http
     */
    public function testConstructorCanAcceptAllMessageParts()
    {
        $content = '';
        $body    = new Stream('php://memory');
        $status  = 302;
        $headers = array(
            'location' => array( 'http://example.com/' ),
        );

        $response = new Response($content, $status, $headers);
        $response->setBody($body);
        $this->assertSame($body, $response->getBody());
        $this->assertEquals(302, $response->getStatusCode());
        $responseHeaders = $response->getHeaders();
        $this->assertEquals($headers['location'], $responseHeaders['location']);
    }

    /**
     * @return array
     */
    public function invalidStatus()
    {
        return array(
            'true'       => array( true ),
            'false'      => array( false ),
            'float'      => array( 100.1 ),
            'bad-string' => array( 'Two hundred' ),
            'array'      => array( array( 200 ) ),
            'object'     => array( (object) array( 'statusCode' => 200 ) ),
            'too-small'  => array( 1 ),
            'too-big'    => array( 600 ),
        );
    }

    /**
     * @group http
     * @dataProvider invalidStatus
     */
    public function testConstructorRaisesExceptionForInvalidStatus($code)
    {
        $this->setExpectedException('InvalidArgumentException', 'Invalid status code');
        new Response('', $code);
    }

    /**
     * @group http
     */
    public function testConstructorIgnoresInvalidHeaders()
    {
        $invalidHeaders = array(
            array( 'INVALID' ),
            'x-invalid-null'   => null,
            'x-invalid-true'   => true,
            'x-invalid-false'  => false,
            'x-invalid-int'    => 1,
            'x-invalid-object' => (object) array('INVALID'),
        );
        $validHeaders = array(
            'x-valid-string' => array( 'VALID' ),
            'x-valid-array'  => array( 'VALID' ),
        );
        $response        = new Response('', 200, array_merge($invalidHeaders, $validHeaders));
        $responseHeaders = $response->getHeaders();

        foreach (array_keys($invalidHeaders) as $k) {
            $this->assertArrayNotHasKey($k, $responseHeaders);
        }

        foreach ($validHeaders as $k => $v) {
            $this->assertEquals($v, $responseHeaders[$k]);
        }
    }
}
