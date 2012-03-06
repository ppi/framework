<?php
/**
 * Form class will help in automating rendering forms
 *
 * @author    Paul Dragoonis <dragoonis@php.net>
 * @license   http://opensource.org/licenses/mit-license.php MIT
 * @package   Form
 * @link      www.ppiframework.com
 *
 */
namespace PPI\Form\Tag;
use PPI\Form\Tag;
class Textarea extends Tag {

	/**
	 * The textarea value
	 *
	 * @var string
	 */
	protected $_value = '';

	/**
	 * The constructor
	 *
	 * @param array $options
	 */
	function __construct(array $options = array()) {

		if(isset($options['value'])) {
			$value = $options['value'];
			unset($options['value']);
			$this->_value = $value;
		}
		$this->_attributes = $options;
	}

	/**
	 * Set the value of this field
	 *
	 * @param string $value
	 * @return void
	 */
	function setValue($value) {
		$this->_value = $value;
	}

	/**
	 * Get the value of this field.
	 *
	 * @return string
	 */
	function getValue() {
		return $this->_value;
	}

	/**
	 * Render this tag
	 *
	 * @return string
	 */
	function render() {
		return '<textarea ' . $this->buildAttrs() . '>' . $this->getValue() . '</textarea>';
	}
}
