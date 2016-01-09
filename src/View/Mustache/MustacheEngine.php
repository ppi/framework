<?php
/**
 * This file is part of the PPI Framework.
 *
 * @copyright   Copyright (c) 2012 Paul Dragoonis <paul@ppi.io>
 * @license     http://opensource.org/licenses/mit-license.php MIT
 *
 * @link        http://www.ppi.io
 */

namespace PPI\Framework\View\Mustache;

use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Templating\TemplateNameParserInterface;

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
     * @throws \InvalidArgumentException if the template does not exist
     * @throws \RuntimeException         if the template cannot be rendered
     *
     * @return string The evaluated template as a string
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
     * @return bool true if the template exists, false otherwise
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
     * @return bool True if this class supports the given resource, false otherwise
     */
    public function supports($name)
    {
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
     * @throws \InvalidArgumentException if the template does not exist
     *
     * @return \Mustache_Template A \Mustache_Template instance
     */
    protected function load($name)
    {
        if ($name instanceof \Mustache_Template) {
            return $name;
        }

        return $this->mustache->loadTemplate($name);
    }

    /**
     * Adding the addGlobal() call to keep things compliant with the existing systems relying on global vars.
     * This method will be removed as of PPI 2.1.
     *
     * @param string $key
     * @param mixed  $val
     *
     * @return bool
     */
    public function addGlobal($key, $val)
    {
        return false;
    }
}
