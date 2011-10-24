<?php

/**
 *
 * @version   1.0
 * @author    Paul Dragoonis <dragoonis@php.net>
 * @license   http://opensource.org/licenses/mit-license.php MIT
 * @copyright Digiflex Development
 * @package   Model
 */

class PPI_Model_Auth extends PPI_Model {
	protected $_name = 'ppi_auth';
	protected $_primary = 'id';
	public $_addEditFormStructure = array(
			'fields' => array(
				'controller'    => array('type' => 'dropdown', 'label' => 'Controller', 'options' => ''),
				'method'        => array('type' => 'text', 'label' => 'Method (optional)', 'size' => 30),
				'role_name'     => array('type' => 'dropdown', 'label' => 'User Role', 'options' => ''),
				'type'     		=> array('type' => 'dropdown', 'label' => 'Access Type', 'options' => array('1' => 'allow', '2' => 'deny')),
				),
			'rules' => array(
				'controller'    => array('type' => 'required', 'message' => 'Controller cannot be blank')
		)
	);

	function __construct() {
		parent::__construct($this->_name, $this->_primary);
	}

	/**
	 * Build up a data structure of all the auth rules defined
	 * @param string $p_sController
	 * @param string $p_sMethod
	 * @return array
	 */
	function getAuths($p_sController='') {
		$aFilter = array();
		if($p_sController != '') {
			$aFilter[] 	= $this->getFilter('controller','=', $p_sController);
			$aFilter[] 	= $this->getFilter('role_name', '=', getRoleType());
		}
		$aAuths 	= $this->getList($aFilter, 'controller, method');
		$aNewAuths 	= array();
		foreach($aAuths as $key => $aAuth) {
			$aNewAuths[$aAuth['controller']][] = $aAuth;
		}
		return $aNewAuths;
	}

	/**
	 * It will check the data structure from self::getAuths and try to find a rule for this scenario
	 * Depending if its allow or deny, it will return a boolean.
	 * By default, access is denied, so if what it's looking for isn't found, then it will be denied.
	 *
	 * @param string $p_sController
	 * @param string $p_sMethod
	 * @return boolean
	 */
	function verifyUrlAuth($p_sController, $p_sMethod = '') {
		$aAuthData = $this->getAuths($p_sController);
		// We need rules for the controller.
		if(array_key_exists($p_sController, $aAuthData)) {
			$aAuthData 	= $aAuthData[$p_sController];
			$bFound 	= false;
			// Firstly we loop through all method bound rules
			foreach($aAuthData as $key => $aAuth) {
				if($aAuth['method'] == '') {
					continue;
				}
				if($aAuth['method'] == $p_sMethod) {
					$bFound = true;
				}
			}
			// Secondly we move onto global controller rules. if a method rule hasn't been found already
			if($bFound !== true) {
				foreach($aAuthData as $key => $aAuth) {
					if($aAuth['method'] == '') {
						$bFound = true;
					}
				}
			}
			if($bFound === true) {
				if(getRoleType() == $aAuth['role_name']) {
					return ($aAuth['type'] == 'allow') ? true : false;
				}
				return false;
			}
		}
		return false;
	}
	function deleteRecord($p_iAuthID) {
		return $this->delRecord($this->_name, $this->_primary, $p_iAuthID);
	}
}