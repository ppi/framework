<?php
/**
 * @version   1.0
 * @author    Paul Dragoonis <dragoonis@php.net>
 * @license   http://opensource.org/licenses/mit-license.php MIT
 * @copyright Digiflex Development
 * @package   Model
 * @link      www.ppiframework.com
 */
class PPI_Model_Form_Field extends PPI_Model {

	private $_fieldName;
	private $_fieldID;

	/**
	 * Primary key
	 * @var string $_primary
	 */
	private $_primary = 'id';

	/**
	 * Table Name
	 * @var string $_name
	 */
	public $_name = 'ppi_fb_field';

	function __construct() {
		parent::__construct($this->_name, $this->_primary);
	}

	function getRules() {

	}

	/**
	 * GEt attributes for a field
	 * @param integer $p_iFieldID Field ID
	 * @return array
	 */
	function getAttributes($p_iFieldID) {
		$aAttrs 		= $this->select()
							->from('ppi_fb_field_attribute')
							->where('field_id = '.$this->quote($p_iFieldID))
							->getList();
		$aAttributes 	= array();
		foreach($aAttrs as $aAttr) {
			$aAttributes[$aAttr['key']] = $aAttr['value'];
		}
		return $aAttributes;
	}

	/**
	 * Get a list of field types
	 * @return array
	 */
	function getFieldTypes() {
		$select 		= $this->select()->from('ppi_fb_field_type');
		$aFieldTypes 	= $select->getList($select);
		$aFields		= array();
		foreach($aFieldTypes as $aField) {
			$aFields[$aField['id']] = $aField['type'];
		}
		return !empty($aFieldTypes) ? $aFields : array();
	}

	/**
	 * Get a field from the db
	 * @param integer $p_iFieldID The Field ID
	 * @return
	 */
	function getField($p_iFieldID) {
		return $this->select()
				->from($oField->_name)
				->where("id = '".$p_iFieldID."'")
				->getList($select);
	}

}
