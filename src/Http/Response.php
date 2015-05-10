<?php
/**
 * This file is part of the PPI Framework.
 *
 * @copyright  Copyright (c) 2011-2015 Paul Dragoonis <paul@ppi.io>
 * @license    http://opensource.org/licenses/mit-license.php MIT
 *
 * @link       http://www.ppi.io
 */

namespace PPI\Framework\Http;

use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Symfony\Component\HttpFoundation\Response as SymfonyHttpResponse;

/**
 * HTTP response encapsulation.
 *
 * Responses are considered immutable; all methods that might change state are implemented such that they retain the
 * internal state of the current message and return a new instance that contains the changed state.
 *
 * Adapted from {@link https://github.com/phly/http/blob/master/src/Response.php} by Matthew Weier O'Phinney.
 *
 * @author Vítor Brandão <vitor@ppi.io>
 */
class Response extends SymfonyHttpResponse implements ResponseInterface
{
    /**
     * Map of normalized header name to original name used to register header.
     *
     * @var array
     */
    protected $headerNames = array();

    /**
     * @var string
     */
    protected $protocolVersion = '1.1';

    /**
     * @var StreamInterface
     */
    protected $stream;

    /**
     * Constructor.
     *
     * @param mixed $content The response content, see setContent()
     * @param int   $status  The response status code
     * @param array $headers An array of response headers
     *
     * @throws \InvalidArgumentException When the HTTP status code is not valid
     *
     * @api
     */
    public function __construct($content = '', $status = 200, $headers = array())
    {
        if (null !== $status) {
            $this->validateStatus($status);
        }

        list($headerNames, $headers) = $this->filterHeaders($headers);
        parent::__construct($content, $status, $headers);
        $this->headerNames = $headerNames;
    }

    /**
     * @param string $protocolVersion
     *
     * @return $this
     */
    public function setProtocolVersion($protocolVersion)
    {
        $this->protocolVersion = $protocolVersion;

        return $this;
    }

    /**
     * Retrieves the HTTP protocol version as a string.
     *
     * The string MUST contain only the HTTP version number (e.g., "1.1", "1.0").
     *
     * @throws UnexpectedValueException
     *
     * @return string HTTP protocol version.
     */
    public function getProtocolVersion()
    {
        if (null === $this->protocolVersion) {
            $version = end(explode('/', $this->server->get('SERVER_PROTOCOL')));
            if (!$version || !is_numeric($version) || ((float) $version == (int) $version)) {
                throw new UnexpectedValueException('Unexpected protocol version');
            }

            $this->protocolVersion = $version;
        }

        return $this->protocolVersion;
    }

    /**
     * Create a new instance with the specified HTTP protocol version.
     *
     * The version string MUST contain only the HTTP version number (e.g.,
     * "1.1", "1.0").
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return a new instance that has the
     * new protocol version.
     *
     * @param string $version HTTP protocol version
     *
     * @return self
     */
    public function withProtocolVersion($version)
    {
        $new = clone $this;
        $new->setProtocolVersion($version);

        return $new;
    }

    /**
     * Checks if a header exists by the given case-insensitive name.
     *
     * @param string $header Case-insensitive header name.
     *
     * @return bool Returns true if any header names match the given header
     *              name using a case-insensitive string comparison. Returns false if
     *              no matching header name is found in the message.
     */
    public function hasHeader($header)
    {
        $header = strtolower($header);

        return $this->headers->has($header);
    }

    /**
     * Create a new instance with the provided header, replacing any existing
     * values of any headers with the same case-insensitive name.
     *
     * The header name is case-insensitive. The header values MUST be a string
     * or an array of strings.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return a new instance that has the
     * new and/or updated header and value.
     *
     * @param string          $header Header name
     * @param string|string[] $value  Header value(s).
     *
     * @throws InvalidArgumentException for invalid header names or values.
     *
     * @return self
     */
    public function withHeader($header, $value)
    {
        if (is_string($value)) {
            $value = array($value);
        }

        if (! is_array($value) || ! $this->arrayContainsOnlyStrings($value)) {
            throw new InvalidArgumentException(
                'Invalid header value; must be a string or array of strings'
            );
        }

        $normalized                    = strtolower($header);
        $new                           = clone $this;
        $new->headerNames[$normalized] = $header;
        $new->headers->set($header, $value);

        return $new;
    }

    /**
     * Creates a new instance, with the specified header appended with the
     * given value.
     *
     * Existing values for the specified header will be maintained. The new
     * value(s) will be appended to the existing list. If the header did not
     * exist previously, it will be added.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return a new instance that has the
     * new header and/or value.
     *
     * @param string          $header Header name to add
     * @param string|string[] $value  Header value(s).
     *
     * @throws InvalidArgumentException for invalid header names or values.
     *
     * @return self
     */
    public function withAddedHeader($header, $value)
    {
        if (is_string($value)) {
            $value = array($value);
        }

        if (! is_array($value) || ! $this->arrayContainsOnlyStrings($value)) {
            throw new InvalidArgumentException(
                'Invalid header value; must be a string or array of strings'
            );
        }

        if (! $this->hasHeader($header)) {
            return $this->withHeader($header, $value);
        }

        $normalized = strtolower($header);
        $header     = $this->headerNames[$normalized];

        $new = clone $this;
        $new->headers->set($header, array_merge($this->headers->get($header), $value));

        return $new;
    }

    /**
     * Creates a new instance, without the specified header.
     *
     * Header resolution MUST be done without case-sensitivity.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return a new instance that removes
     * the named header.
     *
     * @param string $header HTTP header to remove
     *
     * @return self
     */
    public function withoutHeader($header)
    {
        if (! $this->hasHeader($header)) {
            return clone $this;
        }

        $normalized = strtolower($header);
        $original   = $this->headerNames[$normalized];

        $new = clone $this;
        $new->headers->remove($original);
        unset($new->headerNames[$normalized]);

        return $new;
    }

    /**
     * @param string $body
     *
     * @return $this
     */
    public function setBody($body = 'php://memory')
    {
        if (! is_string($body) && ! is_resource($body) && ! $body instanceof StreamInterface) {
            throw new InvalidArgumentException(
                'Stream must be a string stream resource identifier, '
                . 'an actual stream resource, '
                . 'or a Psr\Http\Message\StreamInterface implementation'
            );
        }

        $this->stream = ($body instanceof StreamInterface) ? $body : new Stream($body, 'wb+');

        return $this;
    }

    /**
     * Gets the body of the message.
     *
     * @return StreamInterface Returns the body as a stream.
     */
    public function getBody()
    {
        return $this->stream;
    }

    /**
     * Create a new instance, with the specified message body.
     *
     * The body MUST be a StreamInterface object.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return a new instance that has the
     * new body stream.
     *
     * @param StreamInterface $body Body.
     *
     * @throws InvalidArgumentException When the body is not valid.
     *
     * @return self
     */
    public function withBody(StreamInterface $body)
    {
        $new         = clone $this;
        $new->stream = $body;

        return $new;
    }

    /**
     * Extends MessageInterface::getHeaders() to provide request-specific
     * behavior.
     *
     * Retrieves all message headers.
     *
     * This method acts exactly like MessageInterface::getHeaders(), with one
     * behavioral change: if the Host header has not been previously set, the
     * method MUST attempt to pull the host segment of the composed URI, if
     * present.
     *
     * @see MessageInterface::getHeaders()
     * @see UriInterface::getHost()
     *
     * @return array Returns an associative array of the message's headers. Each
     *               key MUST be a header name, and each value MUST be an array of strings.
     */
    public function getHeaders()
    {
        return $this->headers->all();
    }

    /**
     * Extends MessageInterface::getHeader() to provide request-specific
     * behavior.
     *
     * This method acts exactly like MessageInterface::getHeader(), with
     * one behavioral change: if the Host header is requested, but has
     * not been previously set, the method MUST attempt to pull the host
     * segment of the composed URI, if present.
     *
     * @see MessageInterface::getHeader()
     * @see UriInterface::getHost()
     *
     * @param string $name Case-insensitive header field name.
     *
     * @return string
     */
    public function getHeader($name)
    {
        return $this->headers->get($name);
    }

    /**
     * Extends MessageInterface::getHeaderLines() to provide request-specific
     * behavior.
     *
     * Retrieves a header by the given case-insensitive name as an array of strings.
     *
     * This method acts exactly like MessageInterface::getHeaderLines(), with
     * one behavioral change: if the Host header is requested, but has
     * not been previously set, the method MUST attempt to pull the host
     * segment of the composed URI, if present.
     *
     * @see MessageInterface::getHeaderLines()
     * @see UriInterface::getHost()
     *
     * @param string $header Case-insensitive header field name.
     *
     * @return string[]
     */
    public function getHeaderLines($header)
    {
        $header = strtolower($header);

        if (!$this->headers->has($header)) {
            if ($header === 'host') {
                $host = $this->getHost();

                return $host ? array($host) : array();
            }

            return array();
        }

        return $this->headers->has($header) ?
            array($this->headers->get($header)) : array();
    }
    /**
     * Retrieves a comma-separated string of the values for a single header.
     *
     * This method returns all of the header values of the given
     * case-insensitive header name as a string concatenated together using
     * a comma.
     *
     * NOTE: Not all header values may be appropriately represented using
     * comma concatenation. For such headers, use getHeader() instead
     * and supply your own delimiter when concatenating.
     *
     * If the header does not appear in the message, this method MUST return
     * an empty string.
     *
     * @param string $name Case-insensitive header field name.
     * @return string A string of values as provided for the given header
     *    concatenated together using a comma. If the header does not appear in
     *    the message, this method MUST return an empty string.
     */
    public function getHeaderLine($name)
    {
        return implode(',', $this->getHeaderLines($name));
    }

    /**
     * Create a new instance with the specified status code, and optionally
     * reason phrase, for the response.
     *
     * If no Reason-Phrase is specified, implementations MAY choose to default
     * to the RFC 7231 or IANA recommended reason phrase for the response's
     * Status-Code.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return a new instance that has the
     * updated status and reason phrase.
     *
     * @link http://tools.ietf.org/html/rfc7231#section-6
     * @link http://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml
     *
     * @param integer     $code         The 3-digit integer result code to set.
     * @param null|string $reasonPhrase The reason phrase to use with the
     *                                  provided status code; if none is provided, implementations MAY
     *                                  use the defaults as suggested in the HTTP specification.
     *
     * @throws \InvalidArgumentException For invalid status code arguments.
     *
     * @return self
     */
    public function withStatus($code, $reasonPhrase = null)
    {
        $this->validateStatus($code);

        $response = clone $this;
        $response->setStatusCode($code);
        if (null !== $reasonPhrase) {
            $response->setReasonPhrase($reasonPhrase);
        }

        return $response;
    }

    /**
     * @param $reasonPhrase
     *
     * @return $this
     */
    public function setReasonPhrase($reasonPhrase)
    {
        $this->statusText = $reasonPhrase;

        return $this;
    }

    /**
     * Gets the response Reason-Phrase, a short textual description of the Status-Code.
     *
     * Because a Reason-Phrase is not a required element in a response
     * Status-Line, the Reason-Phrase value MAY be null. Implementations MAY
     * choose to return the default RFC 7231 recommended reason phrase (or those
     * listed in the IANA HTTP Status Code Registry) for the response's
     * Status-Code.
     *
     * @link http://tools.ietf.org/html/rfc7231#section-6
     * @link http://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml
     *
     * @return string|null Reason phrase, or null if unknown.
     */
    public function getReasonPhrase()
    {
        return $this->statusText;
    }

    /**
     * Test that an array contains only strings.
     *
     * @param array $array
     *
     * @return bool
     */
    private function arrayContainsOnlyStrings(array $array)
    {
        return array_reduce($array, array( __CLASS__, 'filterStringValue'), true);
    }

    /**
     * Filter a set of headers to ensure they are in the correct internal format.
     *
     * Used by message constructors to allow setting all initial headers at once.
     *
     * @param array $originalHeaders Headers to filter.
     *
     * @return array Filtered headers and names.
     */
    private function filterHeaders(array $originalHeaders)
    {
        $headerNames = $headers = array();
        foreach ($originalHeaders as $header => $value) {
            if (! is_string($header)) {
                continue;
            }

            if (! is_array($value) && ! is_string($value)) {
                continue;
            }

            if (! is_array($value)) {
                $value = array( $value );
            }

            $headerNames[strtolower($header)] = $header;
            $headers[$header]                 = $value;
        }

        return array( $headerNames, $headers );
    }

    /**
     * Test if a value is a string.
     *
     * Used with array_reduce.
     *
     * @param bool  $carry
     * @param mixed $item
     *
     * @return bool
     */
    private static function filterStringValue($carry, $item)
    {
        return is_string($item) ? $carry : false;
    }

    /**
     * Validate a status code.
     *
     * @param int|string $code
     *
     * @throws InvalidArgumentException on an invalid status code.
     */
    private function validateStatus($code)
    {
        if (! is_numeric($code)
            || is_float($code)
            || $code < 100
            || $code >= 600
        ) {
            throw new InvalidArgumentException(sprintf(
                'Invalid status code "%s"; must be an integer between 100 and 599, inclusive',
                (is_scalar($code) ? $code : gettype($code))
            ));
        }
    }
}
