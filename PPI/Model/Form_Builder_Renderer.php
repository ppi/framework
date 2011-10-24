<?php
if(!defined('BASEPATH')) exit('No direct script access allowed');

/**
	 * @license http://opensource.org/licenses/mit-license.php MIT
	 * @copyright (c) Digiflex Development Team 2008
	 * @version 1.0
	 * @author Paul Dragoonis <paul@digiflex.org>
	 * @since Version 1.0
	 */

class PPI_Model_Form_Builder_Renderer {

	protected $_formStructure = array();
	protected $_formFields = array();
	protected $_elementErrors = array();
	protected $_formAction = '';
	protected $_formName = '';
	protected $_formMethod = '';

	function drawForm() {
		if(!array_key_exists('fields', $this->_formStructure)) {
			throw new PPI_Exception('Error when trying to draw form, unable to find any fields');
		}
		foreach($this->_formStructure['fields'] as $fieldName => $fieldOptions) {
			// we always need a field type so lets check it exists
			if(!array_key_exists('type', $fieldOptions)) {
				throw new PPI_Exception('Error when trying to draw form, no field type found');
			}
			// we always need a label, so lets check it exists
			if(!array_key_exists('label', $fieldOptions)) {
				throw new PPI_Exceptions('Error when trying to draw form, no field label found');
			}
			foreach(array('dropdown', 'radio', 'checkbox') as $tmpField) {
				// dropdowns always need options so lets check that there is some
				if($fieldOptions['type'] == $tmpField && (!array_key_exists('options', $fieldOptions) || !is_array($fieldOptions['options'])) ) {
					throw new PPI_Exception('Error when trying to draw form, no options found for '.$tmpField.': '.$fieldName);
				}
			}
		}
	}
}