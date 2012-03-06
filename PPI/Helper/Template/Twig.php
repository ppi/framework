<?php

/**
 *
 * @version   1.0
 * @author    Paul Dragoonis <dragoonis@php.net>
 * @license   http://opensource.org/licenses/mit-license.php MIT
 * @package   View
 */

require_once SYSTEMPATH . 'Vendor/Twig/Autoloader.php';
class PPI_Helper_Template_Twig implements PPI_Interface_Template {

	/**
	 * The renderer
	 *
	 * @var null|\Twig_Environment
	 */
	private $_renderer = null;

	/**
	 * The render variables
	 *
	 * @var array
	 */
	private $_viewVars = array();

	function __construct() {

		if(isset($options['config'])) {
			$this->_config = $options['config'];
		} else {
			$this->_config = PPI_Helper::getConfig();
		}

		Twig_Autoloader::register();
		$sTheme     = $this->_config->layout->view_theme;
		$this->_renderer = new Twig_Environment(new Twig_Loader_Filesystem(VIEWPATH . "$sTheme/", array(
			'cache' => APPFOLDER . 'Cache/Twig/'
		)));
	}

	/**
	 * Render the twig view
	 *
	 * @throws PPI_Exception
	 * @param string $templateName The Template Name
	 * @return void
	 */
	function render($templateName) {

		// Optional extension for twig templates
		$templateName = PPI_Helper::checkExtension($templateName, $this->getTemplateExtension());
		$sTheme     = $this->_config->layout->view_theme;
		$sPath      = VIEWPATH . "$sTheme/$templateName";
		if(!file_exists($sPath)) {
			throw new PPI_Exception('Unable to load: ' . $sPath . ' file does not exist');
		}
		$template = $this->_renderer->loadTemplate($templateName);
		$template->display($this->_viewVars);
	}

	/**
	 * Assign a variable to the render process
	 *
	 * @param string $key The Key
	 * @param mixed $val The Value
	 * @return void
	 */
	function assign($key, $val) {
		$this->_viewVars[$key] = $val;
	}

	/**
	 * Get the file extension for the twig view.
	 *
	 * @return string
	 */
	function getTemplateExtension() {
		return !empty($this->_config->layout->rendererExt) ? $this->_config->layout->rendererExt : '.html';
	}

	/**
	 * Get the default filename for the master template for this template engine
	 *
	 * @return string
	 */
	function getDefaultMasterTemplate() {
		return 'template.html';
	}

	/**
	 * This is yet to be developed
	 *
	 * @param string $templateName The Template Name
	 * @return bool
	 */
	function templateExists($templateName) {
		return true;
	}

}