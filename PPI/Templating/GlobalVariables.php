<?php
/**
 * This file is part of the PPI Framework.
 *
 * @category    PPI
 * @package     ServiceManager
 * @copyright   Copyright (c) 2012 Paul Dragoonis <paul@ppi.io>
 * @license     http://opensource.org/licenses/mit-license.php MIT
 * @link        http://www.ppi.io
 */

namespace PPI\Templating;

use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * GlobalVariables is the entry point for PPI global variables in PHP/Smarty/Twig
 * templates.
 *
 * @author Vítor Brandão <vitor@ppi.io>
 */
class GlobalVariables
{
    protected $serviceManager;

    /**
     * @param ServiceLocatorInterface $serviceManager
     */
    public function __construct(ServiceLocatorInterface $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }

    /**
     * Returns the current request.
     *
     * @return Symfony\Component\HttpFoundation\Request|void The http request object
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
     * @return Symfony\Component\HttpFoundation\Session\Session|void The session
     */
    public function getSession()
    {
        if ($request = $this->getRequest()) {
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
     * @return Boolean The current debug mode
     */
    public function getDebug()
    {
        return (Boolean) $this->serviceManager->getOption('app.debug');
    }
}
