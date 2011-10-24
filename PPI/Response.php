<?php
/**
 *
 * The PPI Response Class
 *
 * @author    Paul Dragoonis <dragoonis@php.net>
 * @license   http://opensource.org/licenses/mit-license.php MIT
 * @package   Core
 * @link      www.ppiframework.com
 *
 */
namespace PPI;
class Response {

	/**
	 * The charset
	 *
	 * @var string
	 */
	protected $_charset = 'utf-8';
	/**
	 * The JS files for rendering
	 *
	 * @var array
	 */
	protected $_jsFiles = array();
	/**
	 * The CSS files for rendering
	 *
	 * @var array
	 */
	protected $_cssFiles = array();

	/**
	 * The Flash Message for rendering
	 *
	 * @var array
	 */
	protected $_flash = array();

	public function __construct(array $options = array()) {

		if(isset($options['charset'])) {
			$this->_charset = $options['charset'];
		}
		if(isset($options['jsFiles'])) {
			$this->_jsFiles = $options['jsFiles'];
		}
		if(isset($options['cssFiles'])) {
			$this->_cssFiles = $options['cssFiles'];
		}
		if(isset($options['flash'], $options['flash']['mode'], $options['flash']['message'])) {
			$this->setFlash($options['flash']['message'], $options['flash']['mode'] === 'success');
		}

	}

	/**
	 * Append to the list of javascript files to be included
	 *
	 * @param mixed $js
	 * @return void
	 */
	public function addJS($js) {
		$this->addJavascript($js);
	}

	/**
	 * Get the list of JS files
	 *
	 * @return array
	 */
	public function getJSFiles() {
		return $this->_jsFiles;
	}

	/**
	 * Append to the list of stylesheets to be included
	 *
	 * @param mixed $p_mStylesheet This can be an existing array of stylesheets or a string.
	 * @return void
	 */
	public function addStylesheet($p_mStylesheet) {

		if(is_string($p_mStylesheet)) {
				$this->_cssFiles[] = $p_mStylesheet;
		} elseif(is_array($p_mStylesheet)) {
			foreach ($p_mStylesheet as $stylesheet) {
				$this->addStylesheet($stylesheet);
			}
		}
	}

	/**
	 * Append to the list of stylesheets to be included
	 *
	 * @param mixed $css This can be an existing array of stylesheets or a string.
	 * @return void
	 */
	public function addCSS($css) {
		$this->addStylesheet($css);
	}

	/**
	 * Clear the list of added css files
	 *
	 * @return void
	 */
	public function clearCSS() {
		$this->_cssFiles = array();
	}

	/**
	 * Get the list of CSS files
	 *
	 * @return array
	 */
	public function getCSSFiles() {
		return $this->_cssFiles;
	}

	/**
	 * Clear the list of added JS files
	 *
	 * @return void
	 */
	public function clearJS() {
		$this->_jsFiles = array();
	}

	/**
	 * Append to the list of javascript files to be included
	 *
	 * @param mixed $p_mJavascript
	 * @return void
	 */
	public function addJavascript($p_mJavascript) {

		switch (gettype($p_mJavascript)) {
			case 'string':
				$this->_jsFiles[] = $p_mJavascript;
				break;

			case 'array':
				foreach ($p_mJavascript as $javascriptFile) {
					$this->addJavascript($javascriptFile);
				}
				break;
		}
	}

	/**
	 * Set a flash message
	 *
	 * @param string $message
	 * @param bool $success
	 * @return void
	 */
	public function setFlash($message, $success = true) {
		$this->_flash = array(
			'message' => $message,
		    'mode'    => $success ? 'success' : 'failure'
		);
	}

	/**
	 * Get the flash messages
	 *
	 * @return mixed
	 */
	public function getFlash() {
		return $this->_flash;
	}

	/**
	 * Get the flash message and then clear it
	 *
	 * @return void
	 */
	public function getFlashAndClear() {
		$flash = $this->getFlash();
		$this->clearFlash();
		return $flash;
	}

	/**
	 * Clear the flash message
	 *
	 * @return void
	 */
	public function clearFlash() {
		$this->_flash = array();
	}

	/**
	 * Get the charset
	 *
	 * @return string
	 */
	public function getCharset() {
		return $this->_charset;
	}

	/**
	 * Set the charset
	 *
	 * @param string $charset
	 * @return void
	 */
	public function setCharset($charset) {
		$this->_charset = $charset;
	}
}