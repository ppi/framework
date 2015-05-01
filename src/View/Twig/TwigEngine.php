<?php

/**
 * This file is part of the PPI Framework.
 *
 * @copyright  Copyright (c) 2012 Paul Dragoonis <paul@ppi.io>
 * @license    http://opensource.org/licenses/mit-license.php MIT
 *
 * @link       http://www.ppi.io
 */

namespace PPI\Framework\View\Twig;

use PPI\Framework\View\EngineInterface;
use PPI\Framework\View\GlobalVariables;
use PPI\Framework\View\TemplateReference;
use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Templating\StreamingEngineInterface;
use Symfony\Component\Templating\TemplateNameParserInterface;

/**
 * This engine knows how to render Twig templates.
 *
 * @author     Fabien Potencier <fabien@symfony.com>
 * @author     Paul Dragoonis <paul@ppi.io>
 */
class TwigEngine implements EngineInterface, StreamingEngineInterface
{
    /**
     * @todo Add inline documentation.
     *
     * @var type
     */
    protected $environment;

    /**
     * @todo Add inline documentation.
     *
     * @var type
     */
    protected $parser;

    /**
     * @todo Add inline documentation.
     *
     * @var type
     */
    protected $locator;

    /**
     * Constructor.
     *
     * @param \Twig_Environment           $environment A \Twig_Environment instance
     * @param TemplateNameParserInterface $parser      A TemplateNameParserInterface instance
     * @param FileLocatorInterface        $locator     A FileLocatorInterface instance
     * @param GlobalVariables|null        $globals     A GlobalVariables instance or null
     */
    public function __construct(\Twig_Environment $environment, TemplateNameParserInterface $parser, FileLocatorInterface $locator, GlobalVariables $globals = null)
    {
        $this->environment = $environment;
        $this->parser      = $parser;
        $this->locator     = $locator;

        if (null !== $globals) {
            $environment->addGlobal('app', $globals);
        }
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
        try {
            return $this->load($name)->render($parameters);
        } catch (\Twig_Error $e) {
            if ($name instanceof TemplateReference) {
                try {
                    // try to get the real file name of the template where the
                    // error occurred
                    $e->setTemplateFile(sprintf('%s',
                        $this->locator->locate(
                            $this->parser->parse(
                                $e->getTemplateFile()
                            )
                        )
                    ));
                } catch (\Exception $ex) {
                }
            }

            throw $e;
        }
    }

    /**
     * Streams a template.
     *
     * @param mixed $name       A template name or a TemplateReferenceInterface instance
     * @param array $parameters An array of parameters to pass to the template
     *
     * @throws \RuntimeException If the template cannot be rendered
     */
    public function stream($name, array $parameters = array())
    {
        $this->load($name)->display($parameters);
    }

    /**
     * Returns true if the template exists.
     *
     * @param mixed $name A template name
     *
     * @return boolean true if the template exists, false otherwise
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
     * @return boolean True if this class supports the given resource, false otherwise
     */
    public function supports($name)
    {
        if ($name instanceof \Twig_Template) {
            return true;
        }

        $template = $this->parser->parse($name);

        return 'twig' === $template->get('engine');
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
     * Pass methods not available in this engine to the Twig_Environment
     * instance.
     *
     * @param string $name
     * @param array  $args
     *
     * @return mixed
     *
     * @warning This method was added for BC and may be removed in future
     *          releases.
     */
    public function __call($name, $args)
    {
        return call_user_func_array(array($this->environment, $name), $args);
    }

    /**
     * Loads the given template.
     *
     * @param mixed $name A template name or an instance of Twig_Template
     *
     * @throws \InvalidArgumentException if the template does not exist
     *
     * @return \Twig_TemplateInterface A \Twig_TemplateInterface instance
     */
    protected function load($name)
    {
        if ($name instanceof \Twig_Template) {
            return $name;
        }

        try {
            return $this->environment->loadTemplate($name);
        } catch (\Twig_Error_Loader $e) {
            throw new \InvalidArgumentException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
