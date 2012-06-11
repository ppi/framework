<?php

/**
 * The session helper for PPI templating component 
 *
 * @package   Templating
 * @author    Paul Dragoonis <dragoonis@php.net>
 * @license   http://opensource.org/licenses/mit-license.php MIT
 * @link      http://www.ppi.io
 */


namespace PPI\Templating\Helper;

use 
	Symfony\Component\Templating\Helper\Helper,
	Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * SessionHelper provides read-only access to the session attributes.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Paul Dragoonis <paul@ppi.io>
 */
class SessionHelper extends Helper
{
    protected $session;

    /**
     * Constructor.
     *
     * @param Request $request A Request instance
     */
    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    /**
     * Returns an attribute
     *
     * @param string $name    The attribute name
     * @param mixed  $default The default value
     *
     * @return mixed
     */
    public function get($name, $default = null)
    {
        return $this->session->get($name, $default);
    }

    public function getFlash($name, array $default = array())
    {
        return $this->session->getFlashBag()->get($name, $default);
    }

    public function getFlashes()
    {
        return $this->session->getFlashBag()->all();
    }

    public function hasFlash($name)
    {
        return $this->session->getFlashBag()->has($name);
    }

    /**
     * Returns the canonical name of this helper.
     *
     * @return string The canonical name
     */
    public function getName()
    {
        return 'session';
    }
}
