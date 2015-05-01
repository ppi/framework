<?php
/**
 * This file is part of the PPI Framework.
 *
 * @copyright  Copyright (c) 2012 Paul Dragoonis <paul@ppi.io>
 * @license    http://opensource.org/licenses/mit-license.php MIT
 *
 * @link       http://www.ppi.io
 */

namespace PPI\Framework\View;

use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * GlobalVariables is the entry point for PPI global variables in PHP/Smarty/Twig
 * templates.
 *
 * @author     Vítor Brandão <vitor@ppi.io>
 */
class GlobalVariables implements \ArrayAccess
{
    /**
     * @todo Add inline documentation.
     *
     * @var ServiceLocatorInterface
     */
    protected $serviceManager;

    /**
     * Constructor.
     *
     * @param ServiceLocatorInterface $serviceManager
     */
    public function __construct(ServiceLocatorInterface $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }

    /**
     * Returns the current request.
     *
     * @return \Symfony\Component\HttpFoundation\Request|void Http request object
     */
    public function getRequest()
    {
        if ($this->serviceManager->has('request') && $request = $this->serviceManager->get('request')) {
            return $request;
        }
    }

    /**
     * Returns the current session.
     *
     * @return \Symfony\Component\HttpFoundation\Session\Session|void The session
     */
    public function getSession()
    {
        if (($request = $this->getRequest()) != false) {
            return $request->getSession();
        }
    }

    /**
     * Returns the current app environment.
     *
     * @return string The current environment string (e.g 'dev')
     */
    public function getEnvironment()
    {
        return $this->serviceManager->getOption('app.environment');
    }

    /**
     * Returns the current app debug mode.
     *
     * @return boolean The current debug mode
     */
    public function getDebug()
    {
        return (boolean) $this->serviceManager->getOption('app.debug');
    }

    /**
     * @see \ArrayAccess::offsetExists()
     */
    public function offsetExists($property)
    {
        return method_exists($this, 'get' . ucfirst($property));
    }

    /**
     * @see \ArrayAccess::offsetGet()
     */
    public function offsetGet($property)
    {
        return call_user_func(array($this, 'get' . ucfirst($property)));
    }

    /**
     * @see \ArrayAccess::offsetSet()
     */
    public function offsetSet($property, $value)
    {
        throw new \RuntimeException('Usage of ' . __METHOD__ . ' is not allowed');
    }

    /**
     * @see \ArrayAccess::offsetUnset()
     */
    public function offsetUnset($property)
    {
        throw new \RuntimeException('Usage of ' . __METHOD__ . ' is not allowed');
    }
}
