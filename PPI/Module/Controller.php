<?php
/**
 * This file is part of the PPI Framework.
 *
 * @copyright  Copyright (c) 2012 Paul Dragoonis <paul@ppi.io>
 * @license    http://opensource.org/licenses/mit-license.php MIT
 * @link       http://www.ppi.io
 */
namespace PPI\Module;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * The base PPI controller class
 *
 * @author     Paul Dragoonis <paul@ppi.io>
 * @package    PPI
 * @subpackage Module
 */
class Controller implements ServiceLocatorAwareInterface
{
    /**
     * Service Locator
     *
     * @var null|object
     */
    protected $_serviceLocator = null;

    /**
     * Caching the results of results from $this->is() lookups.
     *
     * @var array
     */
    protected $_isCache = array();

    /**
     * The options for this controller
     *
     * @var array
     */
    protected $_options = array();

    /**
     * Controller helpers
     *
     * @var array
     */
    protected $_helpers = array();

    /**
     * Get the request object
     *
     * @return object
     */
    protected function getRequest()
    {
        return $this->_serviceLocator->get('Request');
    }

    /**
     * Get the response object
     *
     * @return object
     */
    protected function getResponse()
    {
        return $this->_serviceLocator->get('Response');
    }

    /**
     * Obtain a controller helper by its key name
     *
     * @param string $helperName
     *
     * @return mixed
     *
     * @throws \InvalidArgumentException
     */
    protected function helper($helperName)
    {
        if (!isset($this->_helpers[$helperName])) {
            throw new \InvalidArgumentException('Unable to locate controller helper: ' . $helperName);
        }

        return $this->_helpers[$helperName];

    }

    /**
     * Set a helper object
     *
     * @param string $helperName
     * @param object $helper
     *
     * @return void
     */
    public function setHelper($helperName, $helper)
    {
        $this->_helpers[$helperName] = $helper;
    }

    /**
     * Returns a server parameter by name.
     *
     * @param string  $key     The key
     * @param mixed   $default The default value if the parameter key does not exist
     * @param boolean $deep    If true, a path like foo[bar] will find deeper items
     *
     * @return string
     */
    protected function server($key = null, $default = null, $deep = false)
    {
        return $key === null ? $this->getServer()->all() : $this->getServer()->get($key, $default, $deep);
    }

    /**
     * Returns a post parameter by name.
     *
     * @param string  $key     The key
     * @param mixed   $default The default value if the parameter key does not exist
     * @param boolean $deep    If true, a path like foo[bar] will find deeper items
     *
     * @return string
     */
    protected function post($key = null, $default = null, $deep = false)
    {
        return $key === null ? $this->getPost()->all() : $this->getPost()->get($key, $default, $deep);
    }

    /**
     * Returns a files parameter by name.
     *
     * @param string  $key     The key
     * @param mixed   $default The default value if the parameter key does not exist
     * @param boolean $deep    If true, a path like foo[bar] will find deeper items
     *
     * @return string
     */
    protected function files($key = null, $default = null, $deep = false)
    {
        return $key === null ? $this->getFiles()->all() : $this->getFiles()->get($key, $default, $deep);
    }

    /**
     * Returns a query string parameter by name.
     *
     * @param string  $key     The key
     * @param mixed   $default The default value if the parameter key does not exist
     * @param boolean $deep    If true, a path like foo[bar] will find deeper items
     *
     * @return string
     */
    protected function queryString($key = null, $default = null, $deep = false)
    {
        return $key === null ? $this->getQueryString()->all() : $this->getQueryString()->get($key, $default, $deep);
    }

    /**
     * Returns a server parameter by name.
     *
     * @param string  $key     The key
     * @param mixed   $default The default value if the parameter key does not exist
     * @param boolean $deep    If true, a path like foo[bar] will find deeper items
     *
     * @return string
     */
    protected function cookie($key = null, $default = null, $deep = false)
    {
        return $key === null ? $this->getCookie()->all() : $this->getCookie()->get($key, $default, $deep);
    }

    /**
     * Get/Set a session value
     *
     * @param string     $key
     * @param null|mixed $default If this is not null, it enters setter mode
     *
     * @return mixed
     */
    protected function session($key = null, $default = null)
    {
        return $key === null ? $this->getSession()->all() : $this->getSession()->get($key, $default);
    }

    /**
     * Shortcut for getting the server object
     *
     * @return object
     */
    protected function getServer()
    {
        return $this->getService('Request')->server;
    }

    /**
     * Shortcut for getting the files object
     *
     * @return object
     */
    protected function getFiles()
    {
        return $this->getService('Request')->files;
    }

    /**
     * Shortcut for getting the cookie object
     *
     * @return object
     */
    protected function getCookie()
    {
        return $this->getService('Request')->cookies;
    }

    /**
     * Shortcut for getting the query string object
     *
     * @return object
     */
    protected function getQueryString()
    {
        return $this->getService('Request')->query;
    }

    /**
     * Shortcut for getting the post object
     *
     * @return object
     */
    protected function getPost()
    {
        return $this->getService('Request')->request;
    }

    /**
     * Shortcut for getting the session object
     *
     * @return mixed
     */
    protected function getSession()
    {
        return $this->getService('session');
    }

    /**
     * Check if a condition 'is' true.
     *
     * @param string $key
     *
     * @return boolean
     *
     * @throws InvalidArgumentException
     */
    protected function is($key)
    {
        switch ($key = strtolower($key)) {

            case 'ajax':
                if (!isset($this->_isCache['ajax'])) {
                    return $this->_isCache['ajax'] = $this->getService('Request')->isXmlHttpRequest();
                }

                return $this->_isCache['ajax'];

            case 'put':
            case 'delete':
            case 'post':
            case 'patch':
                if (!isset($this->_isCache['requestMethod'][$key])) {
                    $this->_isCache['requestMethod'][$key] = $this->getService('Request')->getMethod() === strtoupper($key);
                }

                return $this->_isCache['requestMethod'][$key];

            case 'ssl':
            case 'https':
            case 'secure':
                if (!isset($this->_isCache['secure'])) {
                    $this->_isCache['secure'] = $this->getService('Request')->isSecure();
                }

                return $this->_isCache['secure'];

            default:
                throw new \InvalidArgumentException("Invalid 'is' key supplied: {$key}");

        }

    }

    /**
     * Get the remote users ip address
     *
     * @return string
     */
    protected function getIP()
    {
        return $this->server('REMOTE_ADDR');
    }

    /**
     * Get a routing param's value
     *
     * @param string $param
     *
     * @return mixed
     */
    protected function getRouteParam($param)
    {
        return $this->helper('routing')->getParam($param);
    }

    /**
     * Get the remote users user agent
     *
     * @return string
     */
    protected function getUserAgent()
    {
        return $this->server('HTTP_USER_AGENT');
    }

    /**
     * Set service locator
     *
     * @param ServiceLocatorInterface $serviceLocator
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->_serviceLocator = $serviceLocator;
    }

    /**
     * Get service locator
     *
     * @return ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        return $this->_serviceLocator;
    }

    /**
     * Get a registered service
     *
     * @param string $service
     *
     * @return mixed
     */
    protected function getService($service)
    {
        return $this->getServiceLocator()->get($service);
    }

    /**
     * Get the data source service
     *
     * @return mixed
     */
    protected function getDataSource()
    {
        return $this->getService('DataSource');
    }

    /**
     * Render a template
     *
     * @param string $template The template to render
     * @param array  $params   The params to pass to the renderer
     * @param array  $options  Extra options
     *
     * @return string
     */
    protected function render($template, array $params = array(), array $options = array())
    {
        $renderer = $this->_serviceLocator->get('templating');

        // Helpers
        if (isset($options['helpers'])) {
            foreach ($options['helpers'] as $helper) {
                $renderer->addHelper($helper);
            }
        }

        return $renderer->render($template, $params);
    }

    /**
     * Set Flash Message
     *
     * @param string $flashType The flash type
     * @param string $message   The flash message
     *
     * @return void
     */
    protected function setFlash($flashType, $message)
    {
        $this->getSession()->setFlash($flashType, $message);
    }

    /**
     * Create a RedirectResponse object with your $url and $statusCode
     *
     * @param string  $url
     * @param integer $statusCode
     *
     * @return void
     */
    protected function redirect($url, $statusCode = 302)
    {
        $this->getServiceLocator()->set('Response', new RedirectResponse($url, $statusCode));
    }

    /**
     * Shortcut function for redirecting to a route without manually calling $this->generateUrl()
     * You just specify a route name and it goes there.
     *
     * @param string $route
     *
     * @return void
     */
    protected function redirectToRoute($route, $parameters = array(), $absolute = false)
    {
        $this->redirect($this->getService('Router')->generate($route, $parameters, $absolute));
    }

    /**
     * Generate a URL from the specified route name
     *
     * @param string  $route
     * @param array   $parameters
     * @param boolean $absolute
     *
     * @return string
     */
    protected function generateUrl($route, $parameters = array(), $absolute = false)
    {
        return $this->getService('Router')->generate($route, $parameters, $absolute);
    }

    /**
     * Get the app's global configuration
     *
     * @return mixed
     */
    protected function getConfig()
    {
        return $this->getService('Config');
    }

    /**
     * Set the options for this controller
     *
     * @param array $options
     *
     * @return $this
     */
    public function setOptions($options)
    {
        $this->_options = $options;

        return $this;
    }

    /**
     * Get an option from the controller
     *
     * @param string $option  The option name
     * @param null   $default The default value if the option does not exist
     *
     * @return mixed
     */
    public function getOption($option, $default = null)
    {
        return isset($this->_options[$option]) ? $this->_options[$option] : $default;
    }

    /**
     * Get the environment type, defaulting to 'development' if it has not been set
     *
     * @return string
     */
    public function getEnv()
    {
        return $this->getOption('environment', 'development');
    }

    /**
     * Add a template global variable
     *
     * @param string $param
     * @param mixed  $value
     *
     * @return void
     */
    protected function addTemplateGlobal($param, $value)
    {
        $this->getService('templating')->addGlobal($param, $value);
    }
}
