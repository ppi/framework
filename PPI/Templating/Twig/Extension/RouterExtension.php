<?php
/**
 * This file is part of the PPI Framework.
 *
 * @copyright  Copyright (c) 2012 Paul Dragoonis <paul@ppi.io>
 * @license    http://opensource.org/licenses/mit-license.php MIT
 * @link       http://www.ppi.io
 */
namespace PPI\Templating\Twig\Extension;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Provides integration of the Routing component with Twig.
 *
 * @author     Fabien Potencier <fabien@symfony.com>
 * @package    PPI
 * @subpackage Templating
 */
class RouterExtension extends \Twig_Extension
{
    /**
     * @todo Add inline documentation.
     *
     * @var type
     */
    private $generator;

    /**
     * @todo Add inline documentation.
     *
     * @param UrlGeneratorInterface $generator
     *
     * @return void
     */
    public function __construct(UrlGeneratorInterface $generator)
    {
        $this->generator = $generator;
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions()
    {
        return array(
            'url'  => new \Twig_Function_Method($this, 'getUrl'),
            'path' => new \Twig_Function_Method($this, 'getPath'),
        );
    }

    /**
     * @todo Add inline documentation.
     *
     * @param type $name
     * @param type $parameters
     *
     * @return type
     */
    public function getPath($name, $parameters = array())
    {
        return $this->generator->generate($name, $parameters, false);
    }

    /**
     * @todo Add inline documentation.
     *
     * @param type $name
     * @param type $parameters
     *
     * @return type
     */
    public function getUrl($name, $parameters = array())
    {
        return $this->generator->generate($name, $parameters, true);
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'routing';
    }

}
