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
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamableInterface;
use Psr\Http\Message\UriInterface;
use Symfony\Component\HttpFoundation\Request as SymfonyHttpRequest;
use UnexpectedValueException;

/**
 * HTTP Request encapsulation.
 *
 * Requests are considered immutable; all methods that might change state are implemented such that they retain the
 * internal state of the current message and return a new instance that contains the changed state.
 *
 * Adapted from {@link https://github.com/phly/http/blob/master/src/Request.php}
 * and {@link https://github.com/phly/http/blob/master/src/RequestTrait.php} by Matthew Weier O'Phinney.
 *
 * @author Vítor Brandão <vitor@ppi.io>
 */
class Request extends SymfonyHttpRequest implements RequestInterface
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
     * The request-target, if it has been provided or calculated.
     *
     * @var null|string
     */
    protected $requestTarget;

    /**
     * @var StreamableInterface
     */
    protected $stream;

    /**
     * @var null|UriInterface
     */
    protected $uri;

    /**
     * Supported HTTP methods.
     *
     * @var array
     */
    protected $validMethods = array(
        'CONNECT',
        'DELETE',
        'GET',
        'HEAD',
        'OPTIONS',
        'PATCH',
        'POST',
        'PUT',
        'TRACE',
    );

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
     * Gets the body of the message.
     *
     * @return StreamableInterface Returns the body as a stream.
     */
    public function getBody()
    {
        return $this->stream;
    }

    /**
     * Create a new instance, with the specified message body.
     *
     * The body MUST be a StreamableInterface object.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return a new instance that has the
     * new body stream.
     *
     * @param StreamableInterface $body Body.
     *
     * @throws InvalidArgumentException When the body is not valid.
     *
     * @return self
     */
    public function withBody(StreamableInterface $body)
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
     * Saves the request-target.
     *
     * @param $requestTarget
     *
     * @return $this
     */
    public function setRequestTarget($requestTarget)
    {
        $this->requestTarget = $requestTarget;

        return $this;
    }

    /**
     * Retrieves the message's request target.
     *
     * Retrieves the message's request-target either as it will appear (for
     * clients), as it appeared at request (for servers), or as it was
     * specified for the instance (see withRequestTarget()).
     *
     * In most cases, this will be the origin-form of the composed URI,
     * unless a value was provided to the concrete implementation (see
     * withRequestTarget() below).
     *
     * If no URI is available, and no request-target has been specifically
     * provided, this method MUST return the string "/".
     *
     * @return string
     */
    public function getRequestTarget()
    {
        if (null !== $this->requestTarget) {
            return $this->requestTarget;
        }

        return $this->getRequestUri() ?: '/';
    }

    /**
     * Create a new instance with a specific request-target.
     *
     * If the request needs a non-origin-form request-target — e.g., for
     * specifying an absolute-form, authority-form, or asterisk-form —
     * this method may be used to create an instance with the specified
     * request-target, verbatim.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return a new instance that has the
     * changed request target.
     *
     * @link http://tools.ietf.org/html/rfc7230#section-2.7 (for the various
     *     request-target forms allowed in request messages)
     *
     * @param mixed $requestTarget
     *
     * @throws InvalidArgumentException if the request target is invalid.
     *
     * @return self
     */
    public function withRequestTarget($requestTarget)
    {
        if (preg_match('#\s#', $requestTarget)) {
            throw new InvalidArgumentException(
                'Invalid request target provided; cannot contain whitespace'
            );
        }

        $request = self::create($requestTarget);
        $request->setRequestTarget($requestTarget);

        return $request;
    }

    /**
     * Create a new instance with the provided HTTP method.
     *
     * While HTTP method names are typically all uppercase characters, HTTP
     * method names are case-sensitive and thus implementations SHOULD NOT
     * modify the given string.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return a new instance that has the
     * changed request method.
     *
     * @param string $method Case-insensitive method.
     *
     * @throws \InvalidArgumentException for invalid HTTP methods.
     *
     * @return self
     */
    public function withMethod($method)
    {
        $this->validateMethod($method);
        $new = clone $this;
        $new->setMethod($method);

        return $new;
    }

    /**
     * Retrieves the URI instance or a normalized URI for the Request (string format).
     *
     * This method returns a UriInterface instance when PSR-7 mode is enabled and a string otherwise (Symfony mode).
     *
     * @link http://tools.ietf.org/html/rfc3986#section-4.3
     *
     * @param bool $psr7 Enable or disable PSR-7 mode. Disabled by default.
     *
     * @return UriInterface|string When PSR7 mode is enabled returns a UriInterface instance representing the URI of the
     *                             request, if any.
     */
    public function getUri($psr7 = false)
    {
        if (true === $psr7) {
            if (null === $this->uri) {
                $this->uri = new Uri($this->getRequestUri());
            }

            return $this->uri;
        }

        return parent::getUri();
    }

    /**
     * Create a new instance with the provided URI.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return a new instance that has the
     * new UriInterface instance.
     *
     * @link http://tools.ietf.org/html/rfc3986#section-4.3
     *
     * @param UriInterface $uri New request URI to use.
     *
     * @return self
     */
    public function withUri(UriInterface $uri)
    {
        return self::create((string) $uri);
    }

    /**
     * Validate the HTTP method.
     *
     * @param null|string $method
     *
     * @throws InvalidArgumentException on invalid HTTP method.
     */
    private function validateMethod($method)
    {
        if (null === $method) {
            return true;
        }
        if (! is_string($method)) {
            throw new InvalidArgumentException(sprintf(
                'Unsupported HTTP method; must be a string, received %s',
                (is_object($method) ? get_class($method) : gettype($method))
            ));
        }
        $method = strtoupper($method);
        if (! in_array($method, $this->validMethods, true)) {
            throw new InvalidArgumentException(sprintf(
                'Unsupported HTTP method "%s" provided',
                $method
            ));
        }
    }

    /**
     * Retrieve the host from the URI instance.
     *
     * @return string
     */
    private function getHostFromUri()
    {
        $host  = $this->uri->getHost();
        $host .= $this->uri->getPort() ? ':' . $this->uri->getPort() : '';

        return $host;
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
        return array_reduce($array, [ __CLASS__, 'filterStringValue'], true);
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
        if (! is_string($item)) {
            return false;
        }

        return $carry;
    }
}
