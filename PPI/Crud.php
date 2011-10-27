<?php
/**
 * @author    Paul Dragoonis <dragoonis@php.net>
 * @license   http://opensource.org/licenses/mit-license.php MIT
 * @package   CRUD
 * @link      http://www.ppi.io
 */
namespace PPI;
class Crud {
	
	/**
	 * This function is used to parse/load a yaml file.
	 *
	 * @param string $formName
	 * @return array
	 */
	function getFormStructure($formName) {
		$path = CONFIGPATH . 'Forms/' . $formName . ".yaml";
		if(extension_loaded('yaml')) {
			// do stuff here -- with yaml
			return yaml_parse_file($path);
		} else {

			include_once(VENDORPATH . 'Spyc/spyc.php');
			
			$form = new \PPI\Form();
			
			// use spyc
			$structure = \Spyc::YAMLLoad($path);
			$fields = array();
			if(!empty($structure['fields'])) {
				foreach($structure['fields'] as $fieldName => $field) {
					
					$fieldType = $field['type'];
					$fieldLabel = $field['label'];
					unset($field['type'], $field['label']);
					$fields[$fieldName] = $form->$fieldType($fieldName, $field);
				}
				
				foreach($structure['rules'] as $fieldName => $rule) {
					
					if(isset($fields[$fieldName])) {
						$ruleValue = isset($rule['value']) ? $rule['value'] : null;
						$fields[$fieldName]->setRule($rule['message'], $rule['type'], $ruleValue);
					}
					
				}
			}
			return $fields;
		}
	}
	
}