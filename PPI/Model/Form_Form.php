<?php
/**
 *
 * @version   1.0
 * @author    Paul Dragoonis <dragoonis@php.net>
 * @license   http://opensource.org/licenses/mit-license.php MIT
 * @copyright Digiflex Development
 * @package   Model
 * @link      www.ppiframework.com
 */
class PPI_Model_Form_Form extends PPI_Model {

	private $_name 			= 'ppi_fb_form';
	private $_primary 		= 'id';
	private $_formName 		= '';
	private $_formID		= null;
	private $_formFields 	= array();
	private $_formRules 	= array();
	private $oField   		= null;
	private $oRule  		= null;
	/**
	 * Set the form name and initiate a call to the table specified in $this->_name;
	 * @param string $p_sFormName
	 * @return void
	 */
	function __construct($p_sFormName) {
		$this->_formName = $p_sFormName;
		$this->oField = new PPI_Model_Form_Field();
		$this->oRule  = new PPI_Model_Form_Rule($this->_formName);
		parent::__construct($this->_name, $this->_primary);
	}

	/**
	 * Get the form information and set it to the class properties
	 * @return void
	 * @throws PPI_Exception
	 */
	function setFormData() {
		$aFormData = $this->getList('name = ' . $this->quote($this->_formName));
		ppi_dump($aFormData); exit;
		if(count($aFormData) > 0) {
			$aFormData 				= $aFormData[0];
			$this->_storageTable 	= $aFormData['table'];
			$this->_description 	= $aFormData['description'];
			$this->_submitText 		= $aFormData['submit_text'];
			$this->_formID 			= $aFormData['id'];
		} else {
			throw new PPI_Exception('Form: '.$this->_formName . ' doesn\'t exist');
		}
	}

	/**
	 * Get all the fields from the form
	 * @todo: Decide wether this will return the fields, set the object property or both.
	 * @return void
	 */
	function getFields() {

		$aFieldTypes 	= $this->oField->getFieldTypes();
		$aFields 		= $this->oField->select()
							->columns('id, type_id, name, label')
							->from($this->oField->_name)
							->where("form_name = '".$this->_formName."'")
							->order('`order`')
							->getList();

		// Do the field type mapping
		foreach($aFields as $key => $aField) {
			$aFields[$key]['type'] = $aFieldTypes[$aField['type_id']]; // Set the field type
			foreach(array('type_id', 'id') as $unsetField) {
				unset($aFields[$key][$unsetField]);
			}
			$iFieldID = $aField['id'];
			// Get all the attributes and assign it to the
			$aFieldAttributes = $this->oField->getAttributes($iFieldID);
			foreach($aFieldAttributes as $attrkey => $attrval) {
				$aFields[$key][$attrkey] = $attrval;
			}

		}
		$this->_formFields = $aFields;
	}

	/**
	 * Get all the fields from the form
	 * @todo: Decide wether this will return the fields, set the object property or both.
	 * @return void
	 */
	function getRules() {
		$aRuleTypes 	= $this->oRule->getRuleTypes();
		$aRules 		= $this->oRule->select()
							->columns('r.field_id, r.rule_id, r.value, r.error_message, f.name as field_name')
							->from($this->oRule->_name . ' r')
							->innerJoin($this->oField->_name . ' f', 'r.field_id = f.id')
							->where("r.form_name = '".$this->_formName."'")
							->getList();
		$aNewRules		= array();
		// Do the field rule type mapping
		foreach($aRules as $key => $aRule) {
			// Get the rule name and assign it
			$aRules[$key]['name'] = $aRuleTypes[$aRule['rule_id']]['name'];
			$aRules[$key]['field_name'] = $aRule['field_name'];
			// If there is no rule message defined at the rule, we take it from the Rule Type
			$aRules[$key]['message'] = ($aRule['error_message'] != '') ? $aRule['error_message'] : $aRuleTypes[$aRule['rule_id']]['message'];
			if($aRules[$key]['name'] == 'compare') {
				$aRules[$key]['comparee'] = $aRule['value'];
			}
		}
		$this->_formRules = $aRules;

	}

	/**
	 * Build the entire form structure from the database
	 * @return array
	 */
	function buildForm() {
		$formStructure = array();
		// Retreive the fields, and all their associated options and put into $this->_formFields
		$this->getFields();
		$this->getRules();
		// might wanna call the rules stuff here or might wanna do it in the field iteration
		// Check to see if there is actually any fields to be processed
		if(count($this->_formFields) > 0) {
			$formStructure['fields'] = array();
			foreach($this->_formFields as $key => $aField) {
				$sFieldName = $aField['name'];
				unset($aField['name']);
				$formStructure['fields'][$sFieldName] = $aField;
			}
		}
		if(count($this->_formRules) > 0) {
			$formStructure['rules'] = array();
			foreach($this->_formRules as $key => $aRule) {
				$aNewRule = array(
					'type' 		=> $aRule['name'],
					'message' 	=> $aRule['message']
				);
				if(array_key_exists('comparee', $aRule)) {
					$aNewRule['comparee'] = $aRule['comparee'];
				}
				$formStructure['rules'][$aRule['field_name']][] = $aNewRule;
			}
		}
		return $formStructure;
	}

	/**
	 * Set the form name
	 * @param string $p_sFormName
	 * @return void
	 */
	function setFormName($p_sFormName) {
		$this->_formName = $p_sFormName;
	}

	/**
	 * Get the form name
	 * @return void
	 */
	function getFormName() {
		return $this->_formName;
	}

	/**
	 * Set the form id
	 * @param integer $p_iFormID
	 * @return void
	 */
	function setFormID($p_iFormID) {
		$this->_formID = $p_iFormID;
	}

	/**
	 * Get the form id
	 * @return integer
	 */
	function getFormID() {
		return $this->_formID;
	}

}