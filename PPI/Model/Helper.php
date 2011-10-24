<?php
/**
 * @version   1.0
 * @author    Paul Dragoonis <dragoonis@php.net>
 * @license   http://opensource.org/licenses/mit-license.php MIT
 * @copyright Digiflex Development
 * @package   Model
 * @link      www.ppiframework.com
 */
class PPI_Model_Helper {

    /**
     * The singleton instance variable
     *
     * @var null|PPI_Model_Helper
     */
    protected static $_instance = null;


	function __construct() {
//		$config = PPI_Registry::getInstance()->get('config');
//		parent::__construct($config->system->defaultUserTable, $config->system->defaultUserPK);
	}


    /**
     * The initialise function to create the instance
     * @return void
     */
    protected static function init() {
        self::setInstance(new PPI_Model_Helper());
    }

    /**
     * The function used to initially set the instance
     *
     * @param PPI_Model_Helper $instance
     * @throws PPI_Exception
     * @return void
     */
    static function setInstance(PPI_Model_Helper $instance) {
        if (self::$_instance !== null) {
            throw new PPI_Exception('PPI_Model_Helper is already initialised');
        }
        self::$_instance = $instance;
    }

    /**
     * Obtain the instance if it exists, if not create it
     *
     * @return PPI_Model_Helper
     */
    static function getInstance() {
        if (self::$_instance === null) {
            self::init();
        }
        return self::$_instance;
    }

	/**
	 * This function returns the role name of the user
     *
	 * @return string
	 */
	static function getRoleType() {
		$aUserInfo = PPI_Model_Helper::getInstance()->getAuthData();
		return ($aUserInfo !== false && count($aUserInfo) > 0) ? $aUserInfo['role_name'] : 'guest';
	}


	/**
	 * This function returns the role number of the user
     *
	 * @todo Do a lookup for the guest user ID instead of defaulting to 1
	 * @return integer
	 */
	static function getRoleID() {
	       $aUserInfo = PPI_Model_Helper::getInstance()->getAuthData();
	       return ($aUserInfo !== false && count($aUserInfo) > 0) ? $aUserInfo['role_id'] : 1;
	}

    /**
     * Convert the role name by specifying the role id
     *
     * @static
     * @throws PPI_Exception
     * @param  integer $p_iRoleID The Role ID
     * @return string
     */
	static function getRoleNameFromID($p_iRoleID) {
		$oConfig = PPI_Helper::getConfig();
		$aRoles = array_flip(getRoles());
		if(array_key_exists($p_iRoleID, $aRoles)) {
			return $aRoles[$p_iRoleID];
		}
		throw new PPI_Exception('Unknown Role Type: '.$p_sRoleName);
	}


    /**
     * Convert the role ID by specifying the role name
     *
     * @static
     * @throws PPI_Exception
     * @param  string $p_sRoleName The role name
     * @return integer
     */
	static function getRoleIDFromName($p_sRoleName) {
		$oConfig = PPI_Helper::getConfig();
		$aRoles = $oConfig->system->roleMapping->toArray();
		if(array_key_exists($p_sRoleName, $aRoles)) {
			return $aRoles[$p_sRoleName];
		}
		throw new PPI_Exception('Unknown Role Type: '.$p_sRoleName);
	}

	/**
	 * Function to recursively trim strings
     * 
	 * @param mixed $input The input to be trimmed
	 * @return mixed
	 */
	function arrayTrim($input){

    	if (!is_array($input)) {
	        return trim($input);
    	}

	    return array_map(array($this, 'arrayTrim'), $input);
	}

    /**
     * PPI Mail Sending Function
     * 
     * @param array $p_aOptions The options for sending to the mail library
     * @uses $p_aOptions[subject, body, toaddr] are all mandatory.
     * @uses Options available are toname
     * @return boolean The result of the mail sending process
     */
    static function sendMail(array $p_aOptions) {
		return PPI_Mail::sendMail($p_aOptions);
    }

	/**
	 * Identify if an email is of valid syntax or not.
	 * @param string $p_sString The email address
	 * @return boolean
	 */
//	static function isValidEmail($p_sString) {
//		return preg_match("/^[^@]+@[a-zA-Z0-9._-]+\.[a-zA-Z]+$/", $p_sString) > 0;
//	}


} // End of class