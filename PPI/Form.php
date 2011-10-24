<?php
/**
 * Form class will help in automating rendering forms
 *
 * @author    Paul Dragoonis <dragoonis@php.net>
 * @license   http://opensource.org/licenses/mit-license.php MIT
 * @package   Form
 * @link      www.ppiframework.com
 */
namespace PPI;
class Form {

	/**
	 * The bind data for the form.
	 *
	 * @var array
	 */
	protected $_bindData = array();

	function __construct() {}

	/**
	 * Create our form
	 *
	 * @param string $action
	 * @param string $method
	 * @param array $options
	 * @return string
	 */
	function create($action = '', $method = '', array $options = array()) {
		return $this->add('form', array('method' => $method, 'action' => $action) + $options);
	}

	/**
	 * Add a text field to our form
	 *
	 * @param string $name
	 * @param array $options
	 * @return string
	 */
	function text($name, array $options = array()) {
		if(!empty($name)) {
			return $this->add('text', array('name' => $name) + $options);
		}
		return '';
	}

	/**
	 * Add a textarea field to our form
	 *
	 * @param string $name
	 * @param array $options
	 * @return string
	 */
	function textarea($name, array $options = array()) {
		if(!empty($name)) {
			return $this->add('textarea', array('name' => $name) + $options);
		}
		return '';
	}

	/**
	 * Add a password field to our form
	 *
	 * @param string $name
	 * @param array $options
	 * @return string
	 */
	function password($name, array $options = array()) {
		if(!empty($name)) {
			return $this->add('password', array('name' => $name) + $options);
		}
		return '';
	}

	/**
	 * Add a checkbox field to our form
	 *
	 * @param string $name
	 * @param array $options
	 * @return string
	 */
	function checkbox($name, array $options = array()) {
		if(!empty($name)) {
			return $this->add('checkbox', array('name' => $name) + $options);
		}
		return '';
	}

	/**
	 * Add a radio field to our form
	 *
	 * @param string $name
	 * @param array $options
	 * @return string
	 */
	function radio($name, array $options = array()) {
		if(!empty($name)) {
			return $this->add('radio', array('name' => $name) + $options);
		}
		return '';
	}

	/**
	 * Add a submit field
	 *
	 * @param string $value
	 * @param array $options
	 * @return void
	 */
	function submit($value = 'Submit', array $options = array()) {
		return $this->add('submit', array('value' => $value) + $options);
	}

	/**
	 * Add a hidden field
	 *
	 * @param string $name
	 * @param array $options
	 * @return string
	 */
	function hidden($name, array $options = array()) {
		return $this->add('hidden', array('name' => $name) + $options);
	}

	/**
	 * Add a select (dropdown) field
	 *
	 * @param string $name
	 * @param array $dropdownValues
	 * @param array $options
	 * @return string
	 */
	function select($name, array $dropdownValues, array $options = array()) {
		return $this->add('select', array(
			'name'           => $name,
			'dropdownValues' => $dropdownValues
		) + $options);
	}

	/**
	 * Add a dropdown field. This is just an alias to $this->select()
	 *
	 * @param string $name
	 * @param array $dropdownValues
	 * @param array $options
	 * @return string
	 */
	function dropdown($name, array $dropdownValues, array $options = array()) {
		return $this->select($name, $dropdownValues, $options);
	}

	/**
	 * Add a field to our form.
	 *
	 * @param string $fieldType
	 * @param array $options
	 * @return void
	 */
	function add($fieldType, array $options = array()) {

		switch($fieldType) {

			case 'form':
				$field = new Form\Tag\Form($options);
				break;

			case 'text':
				$field = new Form\Tag\Text($options);
				break;

			case 'textarea':
				$field = new Form\Tag\Textarea($options);
				break;

			case 'password':
				$field = new Form\Tag\Password($options);
				break;

			case 'submit':
				$field = new Form\Tag\Submit($options);
				break;

			case 'checkbox':
				$field = new Form\Tag\Checkbox($options);
				break;

			case 'radio':
				$field = new Form\Tag\Radio($options);
				break;

			case 'hidden':
				$field = new Form\Tag\Hidden($options);
				break;

			case 'select':
			case 'dropdown':

				if(isset($options['dropdownValues'])) {
					$values = $options['dropdownValues'];
					unset($options['dropdownValues']);
				}
				if(isset($options['selected'])) {
					$selected = $options['selected'];
					unset($options['selected']);
				}

				// @todo revise this, it needs refactored as we have bind data now.
				$field = new Form\Tag\Select($options);
				if(isset($values)) {
					$field->setValues($values);
				}
				if(isset($selected)) {
					$field->setValue($selected);
				}

				break;

			default:
				throw new PPI_Exception('Invalid Field Type: ' . $fieldType);
		}

		// If we have bind data against the current element. Lets apply it.
		if(isset($options['name']) && !empty($this->_bindData) && isset($this->_bindData[$options['name']])) {
			$field->setValue($this->_bindData[$options['name']]);
		}

		return $field;
	}

	/**
	 * Apply some bind data to this form.
	 *
	 * @param array $data
	 * @return void
	 */
	function bind(array $data) {
		$this->_bindData = $data;
	}

	function end() {
		return '</form>';
	}

}