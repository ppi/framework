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

use PPI\Framework\Http\Uri;

/**
 * Class UriTest.
 *
 * Failing tests:
 * - PhlyTest\Http\Uri::testAuthorityOmitsPortForStandardSchemePortCombinations()
 */
class UriTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @group http
     */
    public function testImplementsPsr7UriInterface()
    {
        $r = new \ReflectionClass('PPI\Framework\Http\Uri');
        $this->assertTrue($r->implementsInterface('Psr\Http\Message\UriInterface'));
    }

    /**
     * @group http
     */
    public function testConstructorSetsAllProperties()
    {
        $uri = new Uri('https://user:pass@local.example.com:3001/foo?bar=baz#quz');
        $this->assertEquals('https', $uri->getScheme());
        $this->assertEquals('user:pass', $uri->getUserInfo());
        $this->assertEquals('local.example.com', $uri->getHost());
        $this->assertEquals(3001, $uri->getPort());
        $this->assertEquals('user:pass@local.example.com:3001', $uri->getAuthority());
        $this->assertEquals('/foo', $uri->getPath());
        $this->assertEquals('bar=baz', $uri->getQuery());
        $this->assertEquals('quz', $uri->getFragment());
    }

    /**
     * @group http
     */
    public function testCanSerializeToString()
    {
        $url = 'https://user:pass@local.example.com:3001/foo?bar=baz#quz';
        $uri = new Uri($url);
        $this->assertEquals($url, (string) $uri);
    }

    /**
     * @group http
     */
    public function testWithSchemeReturnsNewInstanceWithNewScheme()
    {
        $uri = new Uri('https://user:pass@local.example.com:3001/foo?bar=baz#quz');
        $new = $uri->withScheme('http');
        $this->assertNotSame($uri, $new);
        $this->assertEquals('http', $new->getScheme());
        $this->assertEquals('http://user:pass@local.example.com:3001/foo?bar=baz#quz', (string) $new);
    }

    /**
     * @group http
     */
    public function testWithUserInfoReturnsNewInstanceWithProvidedUser()
    {
        $uri = new Uri('https://user:pass@local.example.com:3001/foo?bar=baz#quz');
        $new = $uri->withUserInfo('matthew');
        $this->assertNotSame($uri, $new);
        $this->assertEquals('matthew', $new->getUserInfo());
        $this->assertEquals('https://matthew@local.example.com:3001/foo?bar=baz#quz', (string) $new);
    }

    /**
     * @group http
     */
    public function testWithUserInfoReturnsNewInstanceWithProvidedUserAndPassword()
    {
        $uri = new Uri('https://user:pass@local.example.com:3001/foo?bar=baz#quz');
        $new = $uri->withUserInfo('matthew', 'zf2');
        $this->assertNotSame($uri, $new);
        $this->assertEquals('matthew:zf2', $new->getUserInfo());
        $this->assertEquals('https://matthew:zf2@local.example.com:3001/foo?bar=baz#quz', (string) $new);
    }

    /**
     * @group http
     */
    public function testWithHostReturnsNewInstanceWithProvidedHost()
    {
        $uri = new Uri('https://user:pass@local.example.com:3001/foo?bar=baz#quz');
        $new = $uri->withHost('ppi.io');
        $this->assertNotSame($uri, $new);
        $this->assertEquals('ppi.io', $new->getHost());
        $this->assertEquals('https://user:pass@ppi.io:3001/foo?bar=baz#quz', (string) $new);
    }

    /**
     * @return array
     */
    public function validPorts()
    {
        return array(
            'int'       => array( 3000 ),
            'string'    => array( "3000" ),
        );
    }

    /**
     * @group http
     * @dataProvider validPorts
     */
    public function testWithPortReturnsNewInstanceWithProvidedPort($port)
    {
        $uri = new Uri('https://user:pass@local.example.com:3001/foo?bar=baz#quz');
        $new = $uri->withPort($port);
        $this->assertNotSame($uri, $new);
        $this->assertEquals($port, $new->getPort());
        $this->assertEquals(
            sprintf('https://user:pass@local.example.com:%d/foo?bar=baz#quz', $port),
            (string) $new
        );
    }

    /**
     * @return array
     */
    public function invalidPorts()
    {
        return array(
            'null'      => array( null ),
            'true'      => array( true ),
            'false'     => array( false ),
            'string'    => array( 'string' ),
            'array'     => array( array( 3000 ) ),
            'object'    => array( (object) array( 3000 ) ),
            'zero'      => array( 0 ),
            'too-small' => array( -1 ),
            'too-big'   => array( 65536 ),
        );
    }

    /**
     * @group http
     * @dataProvider invalidPorts
     */
    public function testWithPortRaisesExceptionForInvalidPorts($port)
    {
        $uri = new Uri('https://user:pass@local.example.com:3001/foo?bar=baz#quz');
        $this->setExpectedException('InvalidArgumentException', 'Invalid port');
        $new = $uri->withPort($port);
    }

    /**
     * @group http
     */
    public function testWithPathReturnsNewInstanceWithProvidedPath()
    {
        $uri = new Uri('https://user:pass@local.example.com:3001/foo?bar=baz#quz');
        $new = $uri->withPath('/bar/baz');
        $this->assertNotSame($uri, $new);
        $this->assertEquals('/bar/baz', $new->getPath());
        $this->assertEquals('https://user:pass@local.example.com:3001/bar/baz?bar=baz#quz', (string) $new);
    }

    /**
     * @return array
     */
    public function invalidPaths()
    {
        return array(
            'null'      => array( null ),
            'true'      => array( true ),
            'false'     => array( false ),
            'array'     => array( array( '/bar/baz' ) ),
            'object'    => array( (object) array( '/bar/baz' ) ),
            'query'     => array( '/bar/baz?bat=quz' ),
            'fragment'  => array( '/bar/baz#bat' ),
        );
    }

    /**
     * @dataProvider invalidPaths
     */
    public function testWithPathRaisesExceptionForInvalidPaths($path)
    {
        $uri = new Uri('https://user:pass@local.example.com:3001/foo?bar=baz#quz');
        $this->setExpectedException('InvalidArgumentException', 'Invalid path');
        $uri->withPath($path);
    }

    /**
     * @group http
     */
    public function testWithQueryReturnsNewInstanceWithProvidedQuery()
    {
        $uri = new Uri('https://user:pass@local.example.com:3001/foo?bar=baz#quz');
        $new = $uri->withQuery('baz=bat');
        $this->assertNotSame($uri, $new);
        $this->assertEquals('baz=bat', $new->getQuery());
        $this->assertEquals('https://user:pass@local.example.com:3001/foo?baz=bat#quz', (string) $new);
    }

    /**
     * @return array
     */
    public function invalidQueryStrings()
    {
        return array(
            'null'      => array( null ),
            'true'      => array( true ),
            'false'     => array( false ),
            'array'     => array( array( 'baz=bat' ) ),
            'object'    => array( (object) array( 'baz=bat' ) ),
            'fragment'  => array( 'baz=bat#quz' ),
        );
    }

    /**
     * @group http
     * @dataProvider invalidQueryStrings
     */
    public function testWithQueryRaisesExceptionForInvalidQueryStrings($query)
    {
        $uri = new Uri('https://user:pass@local.example.com:3001/foo?bar=baz#quz');
        $this->setExpectedException('InvalidArgumentException', 'Query string');
        $uri->withQuery($query);
    }

    /**
     * @group http
     */
    public function testWithFragmentReturnsNewInstanceWithProvidedFragment()
    {
        $uri = new Uri('https://user:pass@local.example.com:3001/foo?bar=baz#quz');
        $new = $uri->withFragment('qat');
        $this->assertNotSame($uri, $new);
        $this->assertEquals('qat', $new->getFragment());
        $this->assertEquals('https://user:pass@local.example.com:3001/foo?bar=baz#qat', (string) $new);
    }

    /**
     * @return array
     */
    public function authorityInfo()
    {
        return array(
            'host-only'      => array( 'http://foo.com/bar',         'foo.com' ),
            'host-port'      => array( 'http://foo.com:3000/bar',    'foo.com:3000' ),
            'user-host'      => array( 'http://me@foo.com/bar',      'me@foo.com' ),
            'user-host-port' => array( 'http://me@foo.com:3000/bar', 'me@foo.com:3000' ),
        );
    }

    /**
     * @group http
     * @dataProvider authorityInfo
     */
    public function testRetrievingAuthorityReturnsExpectedValues($url, $expected)
    {
        $uri = new Uri($url);
        $this->assertEquals($expected, $uri->getAuthority());
    }

    /**
     * @group http
     */
    public function testCanEmitOriginFormUrl()
    {
        $url = '/foo/bar?baz=bat';
        $uri = new Uri($url);
        $this->assertEquals($url, (string) $uri);
    }

    /**
     * @group http
     */
    public function testSettingEmptyPathOnAbsoluteUriIsEquivalentToSettingRootPath()
    {
        $uri = new Uri('http://example.com/foo');
        $new = $uri->withPath('');
        $this->assertEquals('/', $new->getPath());
    }

    /**
     * @group http
     */
    public function testStringRepresentationOfAbsoluteUriWithNoPathNormalizesPath()
    {
        $uri = new Uri('http://example.com');
        $this->assertEquals('http://example.com/', (string) $uri);
    }

    public function testEmptyPathOnOriginFormIsEquivalentToRootPath()
    {
        $uri = new Uri('?foo=bar');
        $this->assertEquals('/', $uri->getPath());
    }

    /**
     * @group http
     */
    public function testStringRepresentationOfOriginFormWithNoPathNormalizesPath()
    {
        $uri = new Uri('?foo=bar');
        $this->assertEquals('/?foo=bar', (string) $uri);
    }

    /**
     * @return array
     */
    public function invalidConstructorUris()
    {
        return array(
            'null'   => array( null ),
            'true'   => array( true ),
            'false'  => array( false ),
            'int'    => array( 1 ),
            'float'  => array( 1.1 ),
            'array'  => array( array( 'http://example.com/' ) ),
            'object' => array( (object) array( 'uri' => 'http://example.com/' ) ),
        );
    }

    /**
     * @group http
     * @dataProvider invalidConstructorUris
     */
    public function testConstructorRaisesExceptionForNonStringURI($uri)
    {
        $this->setExpectedException('InvalidArgumentException');
        new Uri($uri);
    }

    /**
     * @group http
     */
    public function testMutatingSchemeStripsOffDelimiter()
    {
        $uri = new Uri('http://example.com');
        $new = $uri->withScheme('https://');
        $this->assertEquals('https', $new->getScheme());
    }

    /**
     * @return array
     */
    public function invalidSchemes()
    {
        return array(
            'mailto' => array( 'mailto' ),
            'ftp'    => array( 'ftp' ),
            'telnet' => array( 'telnet' ),
            'ssh'    => array( 'ssh' ),
            'git'    => array( 'git' ),
        );
    }

    /**
     * @group http
     * @dataProvider invalidSchemes
     */
    public function testConstructWithUnsupportedSchemeRaisesAnException($scheme)
    {
        $this->setExpectedException('InvalidArgumentException', 'Unsupported scheme');
        $uri = new Uri($scheme . '://example.com');
    }

    /**
     * @dataProvider invalidSchemes
     */
    public function testMutatingWithUnsupportedSchemeRaisesAnException($scheme)
    {
        $uri = new Uri('http://example.com');
        $this->setExpectedException('InvalidArgumentException', 'Unsupported scheme');
        $uri->withScheme($scheme);
    }

    /**
     * @group http
     */
    public function testPathIsPrefixedWithSlashIfSetWithoutOne()
    {
        $uri = new Uri('http://example.com');
        $new = $uri->withPath('foo/bar');
        $this->assertEquals('/foo/bar', $new->getPath());
    }

    /**
     * @group http
     */
    public function testStripsQueryPrefixIfPresent()
    {
        $uri = new Uri('http://example.com');
        $new = $uri->withQuery('?foo=bar');
        $this->assertEquals('foo=bar', $new->getQuery());
    }

    /**
     * @group http
     */
    public function testStripsFragmentPrefixIfPresent()
    {
        $uri = new Uri('http://example.com');
        $new = $uri->withFragment('#/foo/bar');
        $this->assertEquals('/foo/bar', $new->getFragment());
    }

    /**
     * @return array
     */
    public function standardSchemePortCombinations()
    {
        return array(
            'http'  => array( 'http', 80 ),
            'https' => array( 'https', 443 ),
        );
    }

    /**
     * @return array
     */
    public function mutations()
    {
        return array(
            'scheme'    => array('withScheme', 'https'),
            'user-info' => array('withUserInfo', 'foo'),
            'host'      => array('withHost', 'www.example.com'),
            'port'      => array('withPort', 8080),
            'path'      => array('withPath', '/changed'),
            'query'     => array('withQuery', 'changed=value'),
            'fragment'  => array('withFragment', 'changed'),
        );
    }

    /**
     * @group http
     * @dataProvider mutations
     */
    public function testMutationResetsUriStringPropertyInClone($method, $value)
    {
        $uri    = new Uri('http://example.com/path?query=string#fragment');
        $string = (string) $uri;
        $this->assertAttributeEquals($string, 'uriString', $uri);
        $test = $uri->{$method}($value);
        $this->assertAttributeInternalType('null', 'uriString', $test);
        $this->assertAttributeEquals($string, 'uriString', $uri);
    }

    /**
     * @group http
     */
    public function testPathIsProperlyEncoded()
    {
        $uri      = new Uri();
        $uri      = $uri->withPath('/foo^bar');
        $expected = '/foo%5Ebar';
        $this->assertEquals($expected, $uri->getPath());
    }

    /**
     * @group http
     */
    public function testPathDoesNotBecomeDoubleEncoded()
    {
        $uri      = new Uri();
        $uri      = $uri->withPath('/foo%5Ebar');
        $expected = '/foo%5Ebar';
        $this->assertEquals($expected, $uri->getPath());
    }

    /**
     * @return array
     */
    public function queryStringsForEncoding()
    {
        return array(
            'key-only'        => array('k^ey', 'k%5Eey'),
            'key-value'       => array('k^ey=valu`', 'k%5Eey=valu%60'),
            'array-key-only'  => array('key[]', 'key%5B%5D'),
            'array-key-value' => array('key[]=valu`', 'key%5B%5D=valu%60'),
            'complex'         => array('k^ey&key[]=valu`&f<>=`bar', 'k%5Eey&key%5B%5D=valu%60&f%3C%3E=%60bar'),
        );
    }

    /**
     * @group http
     * @dataProvider queryStringsForEncoding
     */
    public function testQueryIsProperlyEncoded($query, $expected)
    {
        $uri = new Uri();
        $uri = $uri->withQuery($query);
        $this->assertEquals($expected, $uri->getQuery());
    }

    /**
     * @group http
     * @dataProvider queryStringsForEncoding
     */
    public function testQueryIsNotDoubleEncoded($query, $expected)
    {
        $uri = new Uri();
        $uri = $uri->withQuery($expected);
        $this->assertEquals($expected, $uri->getQuery());
    }

    /**
     * @group http
     */
    public function testFragmentIsProperlyEncoded()
    {
        $uri      = new Uri();
        $uri      = $uri->withFragment('/p^th?key^=`bar#b@z');
        $expected = '/p%5Eth?key%5E=%60bar%23b@z';
        $this->assertEquals($expected, $uri->getFragment());
    }

    public function testFragmentIsNotDoubleEncoded()
    {
        $expected = '/p%5Eth?key%5E=%60bar%23b@z';
        $uri      = new Uri();
        $uri      = $uri->withFragment($expected);
        $this->assertEquals($expected, $uri->getFragment());
    }
}
