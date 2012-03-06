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
class Submit extends Tag {

	/**
	 * The constructor
	 *
	 * @param array $options
	 */
	function __construct(array $options = array()) {
		$this->_attributes = $options;
	}

	/**
	 * Set the value of this field
	 *
	 * @param string $value
	 * @return void
	 */
	function setValue($value) {
		$this->_attributes['value'] = $value;
	}

	/**
	 * Get the value of this field.
	 *
	 * @return string
	 */
	function getValue() {
		return $this->_attributes['value'];
	}

	/**
	 * Render this tag
	 *
	 * @return string
	 */
	function render() {
		return '<input type="submit" ' . $this->buildAttrs() . '>';
	}
}
