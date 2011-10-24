<?php
/**
 * @version   1.0
 * @author    Paul Dragoonis <dragoonis@php.net>
 * @license   http://opensource.org/licenses/mit-license.php MIT
 * @copyright Digiflex Development
 * @package   Model
 * @link      www.ppiframework.com
 */
class PPI_Model_Form_Rule extends PPI_Model {

	/**
	 * Table Name
	 * @var string $_name
	 */
	public $_name = 'ppi_fb_rule';

	/**
	 * The Primary Key
	 * @var string $_primary
	 */
	private $_primary = 'id';

	/**
	 * The Form Name
	 * @var string $_formName
	 */
	private $_formName = '';

	/**
	 * The Form ID
	 * @var integer $_formID
	 */
	private $_formID;

	/**
	 * The Form Fields
	 * @var array $_formFields
	 */
	private $_formFields = array();

	function __construct($p_sFormName) {
		$this->_formName = $p_sFormName;
		parent::__construct($this->_name, $this->_primary);
	}

	/**
	 * Get the different types of rules
	 */
	function getRuleTypes() {
		$aRuleTypes = $this->select()->from('ppi_fb_rule_type')->getList();
		$aRules		= array();
		foreach($aRuleTypes as $aRule) {
			$aRules[$aRule['id']] = array('name' => $aRule['name'], 'message' => $aRule['default_error_message']);
		}
		return $aRules;
	}

}
