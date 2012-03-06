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
class Select extends Tag {

	const optionsFormat = '<option %svalue="%s">%s</option>';
	const selectFormat = '<select %s>%s</select>';

	/**
	 * Dropdown Options
	 *
	 * @var array
	 */
	protected $_options = array();

	/**
	 * Selected Dropdown Option
	 *
	 * @var null
	 */
	protected $_selected = null;

	/**
	 * The constructor
	 *
	 * @param array $options
	 */
	function __construct(array $options = array()) {

		if(isset($options['values'])) {
			$this->setValues($options['values']);
			unset($options['values']);
		}
		if(isset($options['value'])) {
			$this->setValue($options['value']);
			unset($options['value']);
		}

		$this->_attributes = $options;
	}


	/**
	 * Set the values of this field
	 *
	 * @param array $options
	 * @return void
	 */
	function setValues(array $options) {
		$this->_options = $options;
	}

	/**
	 * Set the selected value
	 *
	 * @param $value
	 * @return void
	 */
	function setValue($value) {
		$this->_selected = $value;
	}

	/**
	 * Build the dropdown options
	 *
	 * @return void
	 */
	function buildOptions() {
		$html = '';
		foreach($this->_options as $key => $val) {
			$selected = '';
			if($this->_selected !== null && $this->_selected == $val) {
				$selected = 'selected="selected" ';
			}
			$html .= sprintf(self::optionsFormat, $selected, $key, $val);
		}
		return $html;
	}

	/**
	 * Render this tag
	 *
	 * @return string
	 */
	function render() {
		return sprintf(self::selectFormat, $this->buildAttrs(), $this->buildOptions());
	}

}
