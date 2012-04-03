<?php

namespace PPI\Templating\Smarty;

use PPI\Templating\EngineInterface;
use Symfony\Component\Templating\Loader\LoaderInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Templating\TemplateNameParserInterface;

/**
 * EngineInterface is the interface each engine must implement.
 *
 * @author Paul Dragoonis <paul@ppi.io>
 * @author Vítor Brandão <noisebleed@noiselabs.org>
 */

class SmartyEngine implements EngineInterface {
    protected $extensions;
    protected $filters;
    protected $globals;
    protected $loader;
    protected $parser;
    protected $plugins;
    protected $smarty;

    /**
     * Constructor.
     *
     * @param \Smarty                     $smarty    A \Smarty instance
     * @param TemplateNameParserInterface $parser    A TemplateNameParserInterface instance
     * @param LoaderInterface             $loader    A LoaderInterface instance
     * @param array                       $options   An array of \Smarty properties
     */
    public function __construct(\Smarty $smarty, 
								TemplateNameParserInterface $parser, 
								LoaderInterface $loader, 
								array $options = array()) {
		// @todo
		// $this->smarty->addTemplateDir($bundlesTemplateDir);
	}
	
	/**
	 * Loads the given template.
	 *
	 * @param string $name A template name
	 *
	 * @return mixed The resource handle of the template file or template object
	 *
	 * @throws \InvalidArgumentException if the template cannot be found
	 *
	 * @todo Check windows filepaths as defined in
	 * {@link http://www.smarty.net/docs/en/resources.tpl#templates.windows.filepath}.
	 */
	public function load($name)
	{
		if ($name instanceof \Smarty_Internal_Template) {
			return $name;
		}
		
		$template = $this->parser->parse($name);
		
		$template = $this->loader->load($template);
		if (false === $template) {
			throw new \InvalidArgumentException(sprintf('The template "%s" does not exist.', $name));
		}
		
		return (string) $template;
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
		$template = $this->load($name);
		
		// @todo - look into
		$this->registerFilters();
		// @todo - look into
		$this->registerPlugins();
		
		// attach the global variables
		// @todo look into
		//     $parameters = array_replace($this->getGlobals(), $parameters);
		
		/**
		* Assign variables/objects to the templates.
		* To learn more see {@link http://www.smarty.net/docs/en/api.assign.tpl}
		*/
		$this->smarty->assign($parameters);
		
		/**
		* Too learn more see {@link http://www.smarty.net/docs/en/api.fetch.tpl}
		*/
		return $this->smarty->fetch($template);
	}
	
	/**
	 * Returns true if the template exists.
	 *
	 * @param string $name A template name
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
		if ($name instanceof \Smarty_Internal_Template) {
			return true;
		}
		
		$template = $this->parser->parse($name);
		
		// keep 'tpl' for backwards compatibility. remove when tagging '0.2.0'
		return in_array($template->get('engine'), array('smarty', 'tpl'));
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
	
}