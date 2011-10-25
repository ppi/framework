<?php
/**
 * @author    Paul Dragoonis <dragoonis@php.net>
 * @license   http://opensource.org/licenses/mit-license.php MIT
 * @copyright Digiflex Development
 * @package   Model
 * @link      www.ppiframework.com
 */
namespace PPI\Model;

use PPI\Core\CoreException;

class Form  {
	/**
	 * The FormBuilder internal formStructure. This consists of the field and their rules
	 * @var array $_formStructure
	 */
	private $_formStructure = array();

	/**
	 *
	 *
	 * @var array
	 */
	private $_formFields = array();

	/**
	 * The list of errors assigned against a form field
	 * @var array $_elementErrors
	 */
	protected $_elementErrors = array();
	/**
	 * The action attribute of the <form> tag
     *
	 * @var string $_formAction
	 */
	protected $_formAction = '';

	/**
	 * The hooker object to allow user control over the behaviour of FormBuilder
	 *
	 * @var object The FormBuilder Hooker
	 */
	protected $oHooker = null;

	/**
	 * The symbolic name assigned to this current form.
	 *
	 * @var string $_formName
	 */
	protected $_formName = '';

	/**
	 * The method attribute on the <form> tag
	 *
	 * @var string $_formMethod
	 */
	protected $_formMethod = '';

	/**
	 * Whether or not the setDefaults() function has been called
	 *
	 * @var boolean $_defaultsApplied
	 */
	protected $_defaultsApplied = false;

	/**
	 * Whether or not to render the <form> tag
	 *
	 * @var boolean $_renderFormTag
	 */
	protected $_renderFormTag = true;

	/**
	 * Whether or not to render the submit button on the form
	 *
	 * @var boolean $_renderSubmitTag
	 */
	protected $_renderSubmitTag = true;

	/**
	 * Whether or not to perform JS validation when rendering the form.
	 *
	 * @var boolean $renderJSValidation
	 */
	protected $_renderJSValidation = true;

	/**
	 * We use this variable to determine the predefined amount of keys against a field
	 * This means any fields not in this list will be added to the fields declaration
	 * EG: 'class' => 'myClass' will be converted to class="myClass"
	 *
	 * @var array $_reservedKeys
	 */
	protected $_reservedKeys = array('type', 'value', 'label', 'options', 'name');

	/**
	 * To determine if recaptcha has been set on one of the fields
	 *
	 * @var boolean $_isReCaptcha
	 */
	protected $_isReCaptcha	= false;

	/**
	 * Not sure what this is need to identify but i think it's the field name that's been assigned a recaptcha type
	 *
	 * @var array $_captchaFields
	 */
	protected $_captchaFields = array();

	/**
	 * To determine whether or not tineMCE has been initiated on one of the fields
	 *
	 * @var boolean $_isTinyMCEEnabled
	 */
	protected $_isTinyMCEEnabled = false;

	/**
	 * The constructor
	 * Setup the hooker object
	 */
	function __construct() {
		$this->oHooker = new \App\Formbuilder\Hooker();
	}
	/**
	 * Initialise the form with name,action and method.
	 *
	 * @param string $formName
	 * @param string $formAction
	 * @param string $formMethod
	 * @return void
	 */
	function init($formName, $formAction = '', $formMethod='post') {
		$this->_formAction 	= $formAction;
		$this->_formName 	= $formName;
		$this->_formMethod 	= $formMethod;
		$this->oHooker->setFormName($this->_formName);
		$this->oHooker->permissions();
	}

	/**
	 * Add an error against a specific field.
	 *
	 * @param string $elementName
	 * @param string $errorMessage
	 * @return void
	 */
	function setElementError($elementName, $errorMessage) {
		// if we already have an error for this field. lets just keep the first one and ignore the rest
		if(array_key_exists($elementName, $this->_elementErrors)) {
			return;
		}
		$this->_elementErrors[$elementName] = $errorMessage;
	}

	/**
	 * Checks if the form is validated
	 *
	 * @return boolean
	 */
	function isValidated() {
		// Make a hook to the preValidate() form Hooker, which will return a set of key => val errors
		// that are put through to $this->setElementError
		$aHookerErrors = $this->oHooker->preValidate();
		foreach((array) $aHookerErrors as $key => $val) {
			$this->setElementError($key, $val);
		}
		// Only if getFormErrors() returned 0 errors then we call the
		// FormBuilder validateForm as oHooker->preValidate() takes validation precedence
		if($this->getFormErrors(true) < 1) {
			$this->validateForm();
		}
		return !empty($this->_elementErrors) ? false : true;
	}

	/**
	 * Checks if the form has been submitted or not.
	 *
	 * @return boolean
	 */
	function isSubmitted() {

		if(!empty($_POST)) {
			// Take all the fields from the form structure and if they don't exist, then we create them with blank values for smoothness.
			foreach(array_keys($this->_formStructure['fields']) as $key => $val) {
				if(!array_key_exists($val, $_POST)) {
					$_POST[$val] = '';
				}
			}
			// Update the form fields, as the form is submitted
			foreach($_POST as $key => $val) {
				$this->_formFields[$key] = $val;
			}
			return true;
		}
		return false;
	}

	/**
	 * Overwrites the submit label for the form
	 *
	 * @param string $sLabel The Label
	 * @return void
	 */
	function setSubmitLabel($sLabel) {

		if(array_key_exists('submit', $this->_formStructure['fields'])) {
			$this->_formStructure['fields']['submit']['value'] = $sLabel;
		}
	}

	/**
	 * This disables the <form></form> tags from being renered
	 *
	 * @return void
	 */
	function disableForm() {
		$this->_renderFormTag = false;
	}

	/**
	 * Disables the submit button from being rendered
	 *
	 * @return void
	 */
	function disableSubmit() {
		$this->_renderSubmitTag = false;
	}

	/**
	 * Enables the submit button from being rendered
	 *
	 * @return void
	 */
	function enableSubmit() {
		$this->_renderSubmitTag = true;
	}

	/**
	 * This enables the <form></form> tags from being renered
	 *
	 * @return void
	 */
	function enableForm() {
		$this->_renderFormTag = true;
	}

	/**
	 * This gets the submitted values from the form.
	 * If $fields is submitted then it only returns the fields specified. Otherwise gets all fields
	 *
	 * @param array [$fields] Key => Value result of the form
	 * @return array
	 */
	function getSubmitValues($fields = array()) {

		// we can only return the fields specified by $fields or we can return all fields
		if(!empty($fields)) {
			foreach($fields as $field) {
				if(array_key_exists($field, $this->_formFields)) {
					$submitFields[$field] = $this->_formFields[$field];
				}
			}

		// We get all the fields by default
		} else {
			foreach($this->_formFields as $fieldName => $fieldVal) {
				if(array_key_exists($fieldName, $this->_formFields)) {
					$submitFields[$fieldName] = $this->_formFields[$fieldName];
				}
			}
		}

		// Remove all captcha fields from the retreived submit values.
		foreach($this->_captchaFields as $val) {
			if(array_key_exists($val, $submitFields) === true) {
				unset($submitFields[$val]);
				unset($submitFields['recaptcha_challenge_field']);
				unset($submitFields['recaptcha_response_field']);
			}
		}

		// remove our submit button !
		if(array_key_exists('submit', $submitFields) === true) {
			unset($submitFields['submit']);
		}
		// Pass these fields to the hooker to clean up and get rid of anything we don't want
		$submitFields = $this->oHooker->preRetreival($submitFields);

		// Clean the inputs
		$helper = new \PPI\Helper();
		$submitFields = $helper->arrayTrim($submitFields);
		return $submitFields;
	}


	/**
	 * Setting the form structure for formBuilder, this can come from a pre-created array.
	 * If the $structure parameter isn't passed it knows to get this form from the DB.
	 *
	 * @todo If a recaptcha form element is found, then we automatically set a rule for it.
	 * @param false|array $structure The structure, if this is false then it tries to get the structure from the DB
	 * @throw CoreException
	 * @return void
	 */
	function setFormStructure($structure = false) {

		if($structure === false) {
			$oForm       = new PPI\Model\Form\Form($this->_formName);
			$structure   = $this->oHooker->structureModify($oForm->buildForm()); // Build the form from the DB and pass it to the hooker
			// Attribute assignment on the actual form field does not work.
		}
		if(array_key_exists('fields', $structure)) {
			foreach($structure['fields'] as $fieldName => $fieldOptions) {
				foreach(array('label', 'value') as $requiredField) {
					// set the field so other parts of the system can rely that it exists
					if(!array_key_exists($requiredField, $fieldOptions)) {
						$structure['fields'][$fieldName][$requiredField] = '';
					}
				}
				// This is for other parts to go a quick "required" lookup, instead of iterating through the rules
				$structure['fields'][$fieldName]['required'] = false;
				if(isset($structure['rules'][$fieldName])) {
					$aRule = $structure['rules'][$fieldName];
					// due to the possibility of having 1 rule or muliple rules, we make the structure generic for both
					if(!isset($aRule[0]) || !is_array($aRule[0])) {
						$aRule = array($aRule);
					}
					foreach($aRule as $aTmpRule) {
						if($aTmpRule['type'] == 'required') {
							$structure['fields'][$fieldName]['required'] = true; // We have found a rule of type "required".
							break;
						}
					}
				}

				// ------------- ReCaptcha Detection to set a isReCaptcha to true and render the HTML for it. ----------
				if(strtolower($fieldOptions['type']) === 'recaptcha') {
					$this->_isReCaptcha 						= true;
					$this->_captchaFields[]						= $fieldName;
					$oCaptcha 									= new PPI\Model\ReCaptcha();
					$structure['fields'][$fieldName]['value'] 	= $oCaptcha->getHTML();
					$structure['rules'][$fieldName] 			= array(
						'type' 		=> 'recaptcha',
						'message' 	=> 'ReCaptcha guess incorrect, please try again'
					);
					// add a rule for recaptcha
				}
				if($fieldOptions['type'] == 'dropdown' && !array_key_exists('options', $fieldOptions)) {
					$structure['fields'][$fieldName]['options'] = array();
				}

				if(strtolower($fieldOptions['type'])  === 'dropdown') {
					$structure['fields'][$fieldName]['option_keys']   = array_keys($structure['fields'][$fieldName]['options']);
					$structure['fields'][$fieldName]['option_values'] = array_values($structure['fields'][$fieldName]['options']);
				}


				// Set the extra attributes so that any additional keys to this fields array will be applied to the HTML
				// 'class' => 'myclass' would be converted to class="myclass".
				$aExtraOptions = array();
				foreach($fieldOptions as $extraKey => $extraVal) {
					if(!in_array($extraKey, $this->_reservedKeys)) {
						$aExtraOptions[$extraKey] = $extraVal;
					}
				}
				$structure['fields'][$fieldName]['attributes'] = !empty($aExtraOptions) ? $aExtraOptions : array();

			}
			// if our submit button doesn't exist, lets add it!
			if($this->_renderSubmitTag === true && !array_key_exists('submit', $structure['fields'])) {
				$structure['fields']['submit'] = array('type' => 'submit', 'label' => '', 'value' => 'Submit', 'attributes' => array());
			}
			// Hook to the form hooker so we can customise out form structure pre-display
			$this->_formStructure = $this->oHooker->structureModify($structure);
			$this->refreshDefaults();
		} else {
			throw new CoreException("Unable to find any fields when setting form structure for <strong>{$this->_formName}</strong>");
		}
	}


	/**
	 * Sets the default values for the form fields specified
	 *
	 * @param array $defaults
	 * @return void
	 */
	function setDefaults(array $defaults) {

		// if the structure fields have been set
		if(!empty($this->_formStructure) && array_key_exists('fields', $this->_formStructure)) {
			foreach($defaults as $key => $val) {
				if(array_key_exists($key, $this->_formStructure['fields'])) {
					$this->_formStructure['fields'][$key]['value'] = $val;
				}
			}
		} else {
			throw new CoreException("Error setting form defaults");
		}
		$this->refreshDefaults();
	}

	/**
	 * This is used to update the form defaults. Performs a hook to FormHooker->dataModify
	 *
	 * @return void
	 */
	function refreshDefaults() {

		// Get the data, pass it to hooker, then re-apply it.
		$formFields = array_keys($this->_formStructure['fields']);
		foreach($formFields as $formField) {
			$origValues[$formField] = $this->_formStructure['fields'][$formField]['value'];
		}

		// Hook to the data modify
		$modifiedValues = $this->oHooker->dataModify($origValues);
		foreach($modifiedValues as $key => $formField) {
			if($modifiedValues[$key] != '') {
				$this->_formStructure['fields'][$key]['value'] = $modifiedValues[$key];
			}
		}
	}
	/**
	 * Validates an error or an array of errors against one field at a time.
	 *
	 * @return boolean
	 */
	function validateForm() {

		if(!array_key_exists('rules', $this->_formStructure)) {
			return true;
		}
		// Loop through all the rules and get the rule options for each field
		foreach($this->_formStructure['rules'] as $fieldName => $ruleValues) {
			// due to the possibility of having 1 rule or muliple rules, we make the structure generic for both
			if(!isset($ruleValues[0]) || !is_array($ruleValues[0])) {
				$ruleValues = array($ruleValues);
			}
			// could change the above array cast to a cast inside this foreach array_values( (array) $ruleValues)
			foreach(array_values($ruleValues) as $key => $ruleOptions) {
				if(!array_key_exists('type', $ruleOptions) || !array_key_exists('message', $ruleOptions)) {
					continue;
				}
				if(!array_key_exists($fieldName, $this->_formFields)) {
					continue;
				}
				$formField = $this->_formFields[$fieldName];
				switch($ruleOptions['type']) {

					case 'recaptcha':
						// ---------------------- ReCaptcha VALIDATION ---------------------------
						if($this->getFormErrors(true) < 1 && array_key_exists('recaptcha_challenge_field', $this->_formFields) && $this->_isReCaptcha === true) {
							$oCaptcha = new PPI\Model\ReCaptcha();
							$aRet = $oCaptcha->verify($this->_formFields['recaptcha_challenge_field'], $this->_formFields['recaptcha_response_field']);
							if($aRet['valid'] === false) {
								$this->setElementError($fieldName, $ruleOptions['message']);
							}
						}
						break;
						// ---------------- END OF ReCaptcha VALIDATION ---------------------------

					case 'required':
						// eg: if we submit a multi <Select> box it will be an array
						if(is_array($formField) && empty($formField)) {
							$this->setElementError($fieldName, $ruleOptions['message']);
						} elseif(is_string($formField) && $formField == '') {
							$this->setElementError($fieldName, $ruleOptions['message']);
						}
						break;

					case 'email':
						if(!filter_var($formField, FILTER_VALIDATE_EMAIL)) {
							$this->setElementError($fieldName, $ruleOptions['message']);
						}
						break;

					case 'nonzero':
						if($formField == 0) {
							$this->setElementError($fieldName, $ruleOptions['message']);
						}
						break;

					case 'alphanum':
						if(!ctype_alnum($formField)) {
							$this->setElementError($fieldName, $ruleOptions['message']);
						}
						break;

					case 'numeric':
						if(preg_match('/^[0-9]$/', $formField)) {
							$this->setElementError($fieldName, $ruleOptions['message']);
						}
						break;

					case 'nonnumeric':
						if(!preg_match('/^[0-9]$/', $formField)) {
							$this->setElementError($fieldName, $ruleOptions['message']);
						}
						break;

					case 'regex':
						if(!array_key_exists('pattern', $ruleOptions)) {
							throw new CoreException('Error when trying to validate the form: no regex pattern found');
						}
						break;

					case 'compare':
						if(!array_key_exists('comparee', $ruleOptions)) {
							throw new CoreException('Comparee not found for \'compare\' clause on '.$fieldName);
						}
						if(!array_key_exists($ruleOptions['comparee'], $this->_formFields)) {
							throw new CoreException('Error when trying to compare '.$fieldName.'. Unable to find the comparee field, '.$ruleOptions['comparee']);
						}
						if($formField != $this->_formFields[$ruleOptions['comparee']]) {
							$this->setElementError($fieldName, $ruleOptions['message']);
						}
						break;

					case 'minlength':
						if(strlen($formField) < $ruleOptions['value']) {
							$this->setElementError($fieldName, $ruleOptions['message']);
						}
						break;

					case 'maxlength':
						if(strlen($formField) > $ruleOptions['value']) {
							$this->setElementError($fieldName, $ruleOptions['message']);
						}
						break;

					case 'url':
						$pattern = "/^(https?|ftp):\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(\#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i";
						if(preg_match($pattern, $formField) < 1) {
							$this->setElementError($fieldName, $ruleOptions['message']);
						}
						break;

					default:
						throw new CoreException('Error when validating the form, trying to use rule type: '. $ruleOptions['type'] . ' that doesn\'t exist');
						break;
				}
			}
		}

	/*	// hook to update the submit values to the form structure so we don't need to keep running this function everytime we want submittion values
		foreach($this->_formStructure['fields'] as $fieldName => $fieldOptions) {
			$this->_formStructure['fields'][$fieldName]['value'] = $this->_formFields[$fieldName];
		}
	*/
	}

	/**
	 * Get the options for the form
	 *
	 * @return array
	 */
	function getFormDetails() {
		return array(
			'formMethod' 		 => $this->_formMethod,
			'formAction' 		 => $this->_formAction,
			'formName' 			 => $this->_formName,
			'renderFormTag'		 => $this->_renderFormTag,
			'renderSubmitTag'	 => $this->_renderSubmitTag,
			'renderTinyMCE'		 => $this->_isTinyMCEEnabled,
			'renderJSValidation' => $this->_renderJSValidation
		);
	}

	/**
	 * Get the form structure
	 *
	 * @return array
	 */
	function getFormStructure() {
		return $this->_formStructure;
	}

	/**
	 * Get the combined information to be passed to the view renderer
	 *
	 * @return array
	 */
	function getRenderInformation() {

		return array(
			'formStructure' => $this->getFormStructure(),
			'formErrors' 	=> $this->getFormErrors(),
			'formDetails' 	=> $this->getFormDetails()
		);
	}

	/**
	 * Get the form errors
	 *
	 * @param boolean $count Default is false. If false will give the errors, else a count of the errors
	 * @return array
	 */
	function getFormErrors($count = false) {
		return ($count === false) ? $this->_elementErrors : count($this->_elementErrors);
	}

	/**
	 * Get the form name
	 *
	 * @return integer
	 */
	function getFormName() {
		return $this->_formName;
	}

	/**
	 * Get the form ID
	 *
	 * @return integer
	 */
	function getFormID() {

	}
	
	/**
	 * Enable tinyMCE rendering
	 *
	 * @param boolean $p_sType
	 * @return void
	 */
	function setTinyMCE($p_sType) {

		if(!is_bool($p_sType)) {
			$p_sType = (bool) $p_sType;
		}
		$this->_isTinyMCEEnabled = $p_sType;
	}

	/**
	 * Get if tinyMCE is enabled
	 *
	 * @return boolean
	 */
	function getTinyMCE() {
		return $this->_isTinyMCEEnabled;
	}
}
