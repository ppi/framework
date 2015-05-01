<?php
/**
 * This file is part of the PPI Framework.
 *
 * @copyright  Copyright (c) 2011-2013 Paul Dragoonis <paul@ppi.io>
 * @license    http://opensource.org/licenses/mit-license.php MIT
 *
 * @link       http://www.ppi.io
 */

namespace PPI\Framework\View\Helper;

use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Templating\Helper\Helper;

/**
 * SessionHelper provides read-only access to the session attributes.
 *
 * @author     Fabien Potencier <fabien@symfony.com>
 * @author     Paul Dragoonis <paul@ppi.io>
 */
class SessionHelper extends Helper
{
    /**
     * @todo Add inline documentation.
     *
     * @var SessionInterface
     */
    protected $session;

    /**
     * Constructor.
     *
     * @param SessionInterface $session
     */
    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    /**
     * Returns an attribute.
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

    /**
     * @todo Add inline documentation.
     *
     * @param type  $name
     * @param array $default
     *
     * @return type
     */
    public function getFlash($name, array $default = array())
    {
        return $this->session->getFlashBag()->get($name, $default);
    }

    /**
     * @todo Add inline documentation.
     *
     * @return type
     */
    public function getFlashes()
    {
        return $this->session->getFlashBag()->all();
    }

    /**
     * @todo Add inline documentation.
     *
     * @param type $name
     *
     * @return type
     */
    public function hasFlash($name)
    {
        return $this->session->getFlashBag()->has($name);
    }

    /**
     * @todo Add inline documentation.
     *
     * @return type
     */
    public function hasFlashes()
    {
        return count($this->session->getFlashBag()->peekAll()) > 0;
    }

    /**
     * @todo Add inline documentation.
     *
     * @return string
     */
    public function getName()
    {
        return 'session';
    }
}
