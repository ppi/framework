<?php
/**
 * This file is part of the PPI Framework.
 *
 * @copyright  Copyright (c) 2012 Paul Dragoonis <paul@ppi.io>
 * @license    http://opensource.org/licenses/mit-license.php MIT
 * @link       http://www.ppi.io
 */
namespace PPI\Templating\Helper;

use Symfony\Component\Templating\Helper\Helper,
    Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * RouterHelper manages links between pages in a template context.
 *
 * @author     Fabien Potencier <fabien@symfony.com>
 * @package    PPI
 * @subpackage Templating
 */
class RouterHelper extends Helper
{
    /**
     * @todo Add inline documentation.
     *
     * @var UrlGeneratorInterface
     */
    protected $generator;

    /**
     * Constructor.
     *
     * @param UrlGeneratorInterface $router A Router instance
     *
     * @return void
     */
    public function __construct(UrlGeneratorInterface $router)
    {
        $this->generator = $router;
    }

    /**
     * Generates a URL from the given parameters.
     *
     * @param string  $name       The name of the route
     * @param mixed   $parameters An array of parameters
     * @param boolean $absolute   Whether to generate an absolute URL
     *
     * @return string The generated URL
     */
    public function generate($name, $parameters = array(), $absolute = false)
    {
        return $this->generator->generate($name, $parameters, $absolute);
    }

    /**
     * Returns the canonical name of this helper.
     *
     * @return string The canonical name
     */
    public function getName()
    {
        return 'router';
    }

}
