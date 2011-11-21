<?php

/**
 * @author    Paul Dragoonis <dragoonis@php.net>
 * @license   http://opensource.org/licenses/mit-license.php MIT
 * @link      www.ppiframework.com
 * @package   View
 */
namespace PPI;
use PPI\View\ViewException,
	PPI\File,
	PPI\Core,
	PPI\Registry,
	PPI\Cache\CacheInterface,
	PPI\View\Renderers\RendererInterface,
	PPI\View\Renderers\Twig as RendererTwig,
	PPI\View\Renderers\Smarty as RendererSmarty,
	PPI\View\Renderers\PHPTal as RendererTal,
	PPI\View\Renderers\PHP as RendererPHP;

class View {

	/**
	 * The variables to be rendered into the view file
	 *
	 * @var array
	 */
	protected $_viewParams = array();

	/**
	 * The current set view theme
	 *
	 * @var null|string
	 */
	protected $_viewTheme = null;

	/**
	 * The master template file
	 *
	 * @var null|string
	 */
	protected $_masterTemplateFile = null;

	/**
	 * Default renderer, PHP helper
	 *
	 * @var string $_defaultRenderer
	 */
	private $_defaultRenderer = 'php';

	/**
	 * The active renderer.
	 * This variable is used so we don't have to instantiate the renderer multuple times.
	 *
	 * @var null|object
	 */
	protected $_activeRenderer = null;

	/**
	 * CSS Files to be rendered
	 *
	 * @var array
	 */
	protected $_cssFiles = array();
	/**
	 * Javascript files to be rendered
	 *
	 * @var array
	 */
	protected $_jsFiles = array();

	/**
	 * The constructor
	 *
	 * @todo - When this is instantiated, pass it an options array,
	 * @todo - Get the skeleton app to pass $config->layout->toArray()
	 * @param array $options The options
	 */
	public function __construct(array $options = array()) {

		if (isset($options['view_theme'])) {
			$this->_viewTheme = $options['view_theme'];
		}
		$this->_config = isset($options['config']) ? $options['config'] : Core::getConfig();  
	}

	/**
	 * Load function called from controllers
	 *
	 * @todo Make this alias to $this->render()
	 * @todo look into making this dynamic name rather than 'smarty', 'twig', 'php'
	 * @param string $p_tplFile The template filename
	 * @param array $p_tplParams Optional user defined params
	 * @return void
	 */
	public function load($p_tplFile, array $p_tplParams = array()) {
		$this->render($p_tplFile, $p_tplParams);
	}

	/**
	 * Add a var to the view params
	 *
	 * @param string $p_sKey
	 * @param mixed $p_mVal
	 * @return void
	 */
	public function set($p_sKey, $p_mVal) {
		$this->_viewParams[$p_sKey] = $p_mVal;
	}

	/**
	 * Add a var to the view params by ref
	 *
	 * @param string $p_sKey
	 * @param mixed &$p_mVal
	 * @return void
	 */
	public function setByRef($p_sKey, &$p_mVal) {
		$this->_viewParams[$p_sKey] = &$p_mVal;
	}

	/**
	 * Add multiple vars to the view params
	 *
	 * @param string $p_sKey
	 * @param array $p_mVal
	 * @return void
	 */
	public function setByArray($p_sKey, array $p_mVal) {
		$this->_viewParams = array_merge($this->_viewParams, $p_mVal);
	}
	
	/**
	 * Override the current set theme
	 *
	 * @param string $p_sThemeName
	 * @return void
	 */
	public function theme($p_sThemeName) {
		$this->_viewTheme = $p_sThemeName;
	}

	/**
	 * Get the currently set view theme
	 *
	 * @return string
	 */
	protected function getViewTheme() {

		if (null === $this->_viewTheme) {
			$this->_viewTheme = $this->_config->layout->view_theme;
		}
		return $this->_viewTheme;
	}

	/**
	 * Initialisation for the renderer, assignment of default values, boot up of the master template
	 *
	 * @param PPI_Interface_Template $oTpl Templating renderer. Instance of PPI_Interface_Template
	 * @param string $p_tplFile The template file to render
	 * @param array $p_tplParams Optional user defined parameres
	 * @return mixed
	 */
	public function setupRenderer(RendererInterface $oTpl, $p_tplFile, array $params = array(), array $options = array()) {

		// Default View Values
		foreach ($params as $key => $val) {
			$oTpl->assign($key, $val);
		}

		$p_tplFile = File::checkExtension($p_tplFile, $oTpl->getTemplateExtension());

		// View Directory Preparation By Theme
		$sViewDir = $this->getViewDir();

		// Get the default view vars that come when you load a view page.
		$defaultViewVars = $this->getDefaultRenderValues(array(
			'viewDir'		=> $sViewDir,
			'actionFile'	=> $p_tplFile
		));

		foreach ($defaultViewVars as $varName => $viewVar) {
			$oTpl->assign($varName, $viewVar);
		}

		/*
		  // Flash Messages
		  if(!isset($this->_config->layout->useMessageFlash) ||
		  ($this->_config->layout->useMessageFlash && $this->_config->layout->useMessageFlash == true)) {

		  }
		 */

		// The Scenarios where we only render the individual template and not the 'master template'
		$nativeAjax        = isset($params['core']['is']['ajax']) && $params['core']['is']['ajax'];
		$overrideAjax      = isset($params['isAjax']) && $params['isAjax'];
		$fullLayoutDisable = isset($options['fullLayout']) && $options['fullLayout'] === false;
		$partialLayout     = isset($options['partial']) && $options['partial'] === true;
		if($nativeAjax || $overrideAjax || $fullLayoutDisable || $partialLayout) {
			$template = $p_tplFile;
		} else {
			// Master template
			$template = $this->_masterTemplateFile !== null ? $this->_masterTemplateFile : $oTpl->getDefaultMasterTemplate();
			$template = File::checkExtension($template, $oTpl->getTemplateExtension());
		}

		// Are we loading a template from the cache?
		if(isset($options['cache'], $options['cacheHandler']) && $options['cache']) {

			if(!$options['cacheHandler'] instanceof CacheInterface) {
				throw new ViewException('Unable to use cache handler, it does not implement PPI\Cache\Interface');
			}

			// If our template exists in the cache
			if($this->cachedRenderExists($template, $options)) {
				return $this->getCachedRender($template, $options);
			}

			// Generate our cachename
			$cacheName = $this->createCachedRenderKey($template, $options);

			ob_start();
			$oTpl->render($template);
			$content = ob_get_contents();
			ob_end_clean();

			$ttl = isset($options['cacheTTL']) ? $options['cacheTTL'] : 0;
			$options['cacheHandler']->set($cacheName, $content, $ttl);

			return $content;

		}

		if(isset($options['partial']) && $options['partial']) {
			ob_start();
			$oTpl->render($template); // Lets render baby !!
			$content = ob_get_contents();
			ob_end_clean();
			return $content;
		}
		
		$oTpl->render($template);
	}

	/**
	 * Get the path to the view file dir
	 *
	 * @return string
	 */
	public function getViewDir() {
		return APPFOLDER . 'View/' . $this->getViewTheme() . '/';
	}

	/**
	 * Obtain the list of default view variables
	 *
	 * @todo review making var names not HNC prefixed.
	 * @param array $options
	 * @return array
	 */
	public function getDefaultRenderValues(array $options) {

		$authData = Core::getSession()->getAuthData();
		$request = array('controller' => '', 'method' => '');

		// Sometimes a render is forced before the PPI_Dispatch object has finished instantiating
		// For example if a 404 is thrown inside the routing/dispatch process then this scenario occurs.
		// @todo - re-evaluate this now that namespacing has been included.
		if (Registry::exists('PPI_Dispatch')) {
			$oDispatch = Core::getDispatcher();
			$request = array(
				'controller' => $oDispatch->getControllerName(),
				'method'     => $oDispatch->getMethodName()
			);
		}
		return array(
			'isLoggedIn'	=> !empty($authData),
			'config'		=> $this->_config,
			'request'		=> $request,
			'authData'		=> $authData,
			'baseUrl'		=> $this->_config->system->base_url,
			'fullUrl'		=> Request::getFullUrl(),
			'currUrl'		=> Request::getCurrUrl(),
			'viewDir'		=> $options['viewDir'],
			'actionFile'	=> $options['actionFile'],
			'responseCode'	=> Registry::get('PPI_View::httpResponseCode', 200),
			'authInfo'		=> $authData, // Do not use, just BC stuff
			'aAuthInfo'		=> $authData, // Do not use, just BC stuff.
			'bIsLoggedIn'	=> !empty($authData), // Do not use, just BC stuff
			'oConfig'		=> $this->_config, // Do not use, just BC stuff
		);
	}

	/**
	 * To get a view variable that is set to get rendered. (TBC)
	 *
	 * @param string $key The Key
	 * @return mixed
	 */
	public function get($key) {

		if (isset($this->_viewParams[$key])) {
			return $this->_viewParams[$key];
		}
		throw new ViewException('Unable to find View Key: ' . $key);
	}

	/**
	 * Override the default template file, with optional include for the .php or .tpl extension
	 *
	 * @param string $p_sNewTemplateFile New Template Filename
	 * @return void
	 */
	public function setTemplateFile($p_sNewTemplateFile) {
		$this->_masterTemplateFile = $p_sNewTemplateFile;
	}

	/**
	 * The internal render function, this is called by $this->load('template');
	 *
	 * @param string $template The template name to render
	 * @param array $params Optional Parameters
	 * @param array $options Optional Options
	 * @return void
	 */
	public function render($template, array $params = array(), array $options = array()) {

		$sRenderer = empty($this->_config->layout->renderer) ? $this->_defaultRenderer : $this->_config->layout->renderer;
		return $this->setupRenderer($this->getRenderer($sRenderer), $template, array_merge($params, $this->_viewParams), $options);
	}

	/**
	 * Get the active renderer
	 *
	 * @param string $rendererName The renderer name
	 * @return object
	 */
	protected function getRenderer($rendererName = '') {

		if($this->_activeRenderer !== null) {
			return $this->_activeRenderer;
		}

		switch ($rendererName) {
			case 'smarty':
				$this->_activeRenderer = new RendererSmarty();
				break;

			case 'twig':
				$this->_activeRenderer = new RendererTwig();
				break;

			case 'php':
			default:
				$this->_activeRenderer = new RendererPHP();
				break;
		}

		return $this->_activeRenderer;
	}

	/**
	 * Check if a template exists
	 *
	 * @param string $templateName The template Name
	 * @return bool
	 */
	public function templateExists($templateName) {
		$sRenderer = empty($this->_config->layout->renderer) ? $this->_defaultRenderer : $this->_config->layout->renderer;
		return $this->getRenderer($sRenderer)->templateExists($templateName);
	}

	/**
	 * Check if a cachedRender item exists in the cache
	 *
	 * @param string $template
	 * @param array $options
	 * @return boolean
	 */
	public function cachedRenderExists($template, array $options = array()) {
		$cacheName = $this->createCachedRenderKey($template, $options);
		return $options['cacheHandler']->exists($cacheName);
	}

	/**
	 * Get the cached render file from the cache
	 *
	 * @param $template
	 * @param array $options
	 * @return string
	 */
	public function getCachedRender($template, array $options = array()) {

		$cacheName = $this->createCachedRenderKey($template, $options);
		if($options['cacheHandler']->exists($cacheName)) {
			return $options['cacheHandler']->get($cacheName);
		}
		return '';

	}

	/**
	 * Create a cache key for our cached template
	 *
	 * @param string $template
	 * @param array $options
	 * @return string
	 */
	public function createCachedRenderKey($template, array $options = array()) {
			return (isset($options['cachePrefix']) ? $options['cachePrefix'] : '') . 'ppi_cached_template_'
				. str_replace(array('\\', '/'), '_', $template);
	}

}
