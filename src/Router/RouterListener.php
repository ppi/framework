<?php
/**
 * This file is part of the PPI Framework.
 *
 * @copyright  Copyright (c) 2011-2016 Paul Dragoonis <paul@ppi.io>
 * @license    http://opensource.org/licenses/mit-license.php MIT
 *
 * @link       http://www.ppi.io
 */

namespace PPI\Framework\Router;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Exception\MethodNotAllowedException as MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\RequestMatcherInterface;
use Symfony\Component\Routing\Matcher\UrlMatcherInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RequestContextAwareInterface;

/**
 * Initializes the context from the request and sets request attributes based on a matching route.
 *
 * @see Symfony\Component\HttpKernel\EventListener\RouterListener
 *
 * @author Paul Dragoonis <paul@ppi.io>
 * @author Vítor Brandão <vitor@ppi.io>
 */
class RouterListener
{
    /**
     * @var UrlMatcherInterface|RequestMatcherInterface
     */
    protected $matcher;

    /**
     * @var RequestContext
     */
    protected $context;

    /**
     * @var LoggerInterface|null
     */
    protected $logger;

    /**
     * @var RequestStack|null
     */
    protected $requestStack;

    /**
     * Constructor.
     *
     * @param UrlMatcherInterface|RequestMatcherInterface $matcher      The Url or Request matcher
     * @param RequestContext|null                         $context      The RequestContext (can be null when $matcher implements RequestContextAwareInterface)
     * @param LoggerInterface|null                        $logger       The logger
     * @param RequestStack|null                           $requestStack A RequestStack instance
     */
    public function __construct($matcher, RequestContext $context = null, LoggerInterface $logger = null,
                                RequestStack $requestStack = null)
    {
        if (!$matcher instanceof UrlMatcherInterface && !$matcher instanceof RequestMatcherInterface) {
            throw new \InvalidArgumentException('Matcher must either implement UrlMatcherInterface or RequestMatcherInterface.');
        }

        if (null === $context && !$matcher instanceof RequestContextAwareInterface) {
            throw new \InvalidArgumentException('You must either pass a RequestContext or the matcher must implement RequestContextAwareInterface.');
        }

        $this->matcher      = $matcher;
        $this->context      = $context ?: $matcher->getContext();
        $this->requestStack = $requestStack;
        $this->logger       = $logger;
    }

    /**
     * @param Request $request
     */
    public function match(Request $request)
    {
        // Initialize the context that is also used by the generator (assuming matcher and generator share the same
        // context instance).
        $this->context->fromRequest($request);

        if ($request->attributes->has('_controller')) {
            // Routing is already done.
            return;
        }

        // Add attributes based on the request (routing).
        try {
            // Matching a request is more powerful than matching a URL path + context, so try that first.
            if ($this->matcher instanceof RequestMatcherInterface) {
                $parameters = $this->matcher->matchRequest($request);
            } else {
                $parameters = $this->matcher->match($request->getPathInfo());
            }

            if (null !== $this->logger) {
                $this->logger->info(sprintf('Matched route "%s" (parameters: %s)', $parameters['_route'], $this->parametersToString($parameters)));
            }

            $request->attributes->add($parameters);
            unset($parameters['_route']);
            unset($parameters['_controller']);
            $request->attributes->set('_route_params', $parameters);
        } catch (ResourceNotFoundException $e) {
            $message = sprintf('No route found for "%s %s"', $request->getMethod(), $request->getPathInfo());

            throw new NotFoundHttpException($message, $e);
        } catch (MethodNotAllowedException $e) {
            $message = sprintf('No route found for "%s %s": Method Not Allowed (Allow: %s)', $request->getMethod(),
                $request->getPathInfo(), strtoupper(implode(', ', $e->getAllowedMethods())));
            throw new MethodNotAllowedException($e->getAllowedMethods(), $message);
        }
    }

    /**
     * @param array $parameters
     *
     * @return string
     */
    protected function parametersToString(array $parameters)
    {
        $pieces = array();
        foreach ($parameters as $key => $val) {
            $pieces[] = sprintf('"%s": "%s"', $key, (is_string($val) ? $val : json_encode($val)));
        }

        return implode(', ', $pieces);
    }
}
