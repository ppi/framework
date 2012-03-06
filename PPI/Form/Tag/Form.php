<?php
/**
 * Form class will help in automating rendering forms
 *
 * @author    Paul Dragoonis <dragoonis@php.net>
 * @license   http://opensource.org/licenses/mit-license.php MIT
 * @package   Form
 * @link      www.ppiframework.com
 */
namespace PPI\Form\Tag;
use PPI\Form\Tag;
class Form extends Tag {

	/**
	 * The constructor
	 *
	 * @param array $options
	 */
	function __construct(array $options = array()) {
		$options['action'] = isset($options['action']) ? $options['action'] : '';
		$this->_attributes = $options;
	}

	/**
	 * Render this tag
	 *
	 * @return string
	 */
	function render() {
		$attrs = $this->buildAttrs();
		return "<form $attrs>";
	}

	/**
	 * When echo'ing this tag class, we call render
	 *
	 * @return string
	 */
	function __toString() {
		return $this->render();
	}
}
