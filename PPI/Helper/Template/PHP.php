<?php
/**
 * @author    Paul Dragoonis <dragoonis@php.net>
 * @license   http://opensource.org/licenses/mit-license.php MIT
 * @package   View
 * @package   www.ppiframework.com
 */
class PPI_Helper_Template_PHP implements PPI_Interface_Template {

	/**
	 * The variables that are to be rendered in the View file
	 *
	 * @var array
	 */
	protected $_viewVars = array();

	/**
	 * The config object
	 *
	 * @var null|object
	 */
	protected $_config = null;

	/**
	 * The constructor
	 *
	 * @param array $options
	 */
	function __construct(array $options = array()) {
		if(isset($options['config'])) {
			$this->_config = $options['config'];
		} else {
			$this->_config = PPI_Helper::getConfig();
		}
	}

	/**
	 * Actually load in the view and render it.
	 *
	 * @param string $templateName The filename to load, such as the master template
	 * @return void
	 */
	function render($templateName) {

		$this->checkTemplateExists($templateName);
		foreach($this->_viewVars as $key => $var) {
			$$key = $var;
		}
		include_once($this->getTemplatePath($templateName));
	}

	/**
	 * Get the full path to the template file by providing the template name
	 *
	 * @param string $templateName
	 * @return string
	 *
	 * @todo Have a hashmap class property to store $templateName => path for caching.
	 * This means we don't need to lookup the config or check if an extension persists.
	 */
	function getTemplatePath($templateName) {
		return VIEWPATH
		       . $this->_config->layout->view_theme
		       . DS
		       . PPI_Helper::checkExtension($templateName, EXT);
	}

	/**
	 * Check if a template exists
	 *
	 * @param string $templateName The Template Name
	 * @return bool
	 */
	public function templateExists($templateName) {
		return file_exists($this->getTemplatePath($templateName));
	}

	/**
	 * Check if a template exists. If it does not, throw an exception
	 *
	 * @throws PPI_Exception
	 * @param string $templateName The Template Name
	 * @return void
	 */
	protected function checkTemplateExists($templateName) {
		if(!$this->templateExists($templateName)) {
			throw new PPI_Exception('Unable to load template: ' . $templateName . ' file does not exist');
		}
	}

	/**
	 * Assign a value for this current view
	 *
	 * @param string $key The variable name
	 * @param string $val The variable value
	 * @return void
	 */
	function assign($key, $val) {
		$this->_viewVars[$key] = $val;
	}

	/**
	 * Get the default extension for our view files, config override or defaulting to .php
	 *
	 * @return string
	 */
	function getTemplateExtension() {
		return !empty($this->_config->layout->rendererExt) ? $this->_config->layout->rendererExt : '.php';
	}

	/**
	 * Get the default master template filename
	 *
	 * @return string
	 */
	function getDefaultMasterTemplate() {
		return 'template.php';
	}

	/**
	 * Get the default file extension for templates on this renderer
	 *
	 * @return string
	 */
	function getDefaultExtension() {
		return '.php';
	}

}