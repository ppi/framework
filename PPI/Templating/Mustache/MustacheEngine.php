<?php
/**
 * This file is part of the PPI Framework.
 *
 * @package     Templating
 * @copyright   Copyright (c) 2012 Paul Dragoonis <paul@ppi.io>
 * @license     http://opensource.org/licenses/mit-license.php MIT
 * @link        http://www.ppi.io
 * 
 */

namespace PPI\Templating\Mustache;

use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Bundle\FrameworkBundle\Templating\GlobalVariables;
use Symfony\Component\Templating\TemplateNameParserInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * This engine knows how to render Mustache templates.
 * 
 * @author Justin Hileman <justin@justinhileman.info>
 */
class MustacheEngine implements EngineInterface
{
    protected $mustache;
    protected $parser;

    /**
     * Constructor.
     *
     * @param Mustache_Engine             $mustache A \Mustache_Engine instance
     * @param TemplateNameParserInterface $parser   A TemplateNameParserInterface instance
     */
    public function __construct(\Mustache_Engine $mustache, TemplateNameParserInterface $parser)
    {
        $this->mustache = $mustache;
        $this->parser   = $parser;
    }

    /**
     * Renders a template.
     *
     * @param mixed $name       A template name
     * @param array $parameters An array of parameters to pass to the template
     *
     * @return string The evaluated template as a string
     *
     * @throws \InvalidArgumentException if the template does not exist
     * @throws \RuntimeException         if the template cannot be rendered
     */
    public function render($name, array $parameters = array())
    {
        return $this->load($name)->render($parameters);
    }

    /**
     * Returns true if the template exists.
     *
     * @param mixed $name A template name
     *
     * @return Boolean true if the template exists, false otherwise
     */
    public function exists($name)
    {
        try {
            $this->load($name);
        } catch (\InvalidArgumentException $e) {
            return false;
        }

        return true;
    }

    /**
     * Returns true if this class is able to render the given template.
     *
     * @param string $name A template name
     *
     * @return Boolean True if this class supports the given resource, false otherwise
     */
    public function supports($name)
    {
        var_dump(__FUNCTION__, $name); exit;
        if ($name instanceof \Mustache_Template) {
            return true;
        }

        $template = $this->parser->parse($name);

        return 'mustache' === $template->get('engine');
    }

    /**
     * Renders a view and returns a Response.
     *
     * @param string   $view       The view name
     * @param array    $parameters An array of parameters to pass to the view
     * @param Response $response   A Response instance
     *
     * @return Response A Response instance
     */
    public function renderResponse($view, array $parameters = array(), Response $response = null)
    {
        if (null === $response) {
            $response = new Response();
        }

        $response->setContent($this->render($view, $parameters));

        return $response;
    }

    /**
     * Loads the given template.
     *
     * @param mixed $name A template name or an instance of Mustache_Template
     *
     * @return \Mustache_Template A \Mustache_Template instance
     *
     * @throws \InvalidArgumentException if the template does not exist
     */
    protected function load($name)
    {
        if ($name instanceof \Mustache_Template) {
            return $name;
        }

        return $this->mustache->loadTemplate($name);
    }
}