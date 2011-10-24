<?php
/**
 *
 * @version   1.0
 * @author    Paul Dragoonis <dragoonis@php.net>
 * @license   http://opensource.org/licenses/mit-license.php MIT
 * @copyright Digiflex Development
 * @package   PPI
 */

class PPI_Acl { 

	/**
	 * The stored ACL rules
	 *
	 * @var array $_rules
	 */
	private $_rules = array();
	
	/**
	 * The instance variable
	 *
	 * @var object $_instance
	 */
	private static $_instance = null;
	
	
	/**
	 * Constructor for PPI_Acl
	 *
	 * @param boolean $p_bParse Default is true. If false it will not parse the default framework ini file.
	 */
	public function __construct($p_bParse = true) {
		if($p_bParse === false) {
			return;
		}
		$oConfig   = PPI_Model_Helper::getConfig();
		$oDispatch = PPI_Model_Helper::getDispatcher();
		if(!file_exists(CONFIGPATH . $oConfig->system->acl->filename)) {
			throw new PPI_Exception('Unable to locate access control file');
		}
		$xml = simplexml_load_file(CONFIGPATH . $oConfig->system->acl->filename);
		if($xml === false) {
			throw new PPI_Exception('Error parsing access controls');
		}
		$aRules = array();
		foreach($xml as $rule) {
			$sController = (string) strtolower($rule->attributes()->controller);
			$aRule = array(
				'controller' => $sController,
				'method'     => (string) strtolower($rule->attributes()->method),
				'roles'   => array()
			);
			
			foreach($rule->children() as $role) {
				$sRole = (string) strtolower($role->attributes()->name);
				$aRule['roles'][$sRole] = (string) strtolower($role->attributes()->access);
			}
			$aRules[$sController] = $aRule;
		}
		$this->setRules($aRules);
	}
	
    /**
     * Retrieves the default instance of the ACL, if it doesn't exist then we create it.
     *
     * @return object
     */
    public static function getInstance() {
        if (self::$_instance === null) {
            self::init();
        }
        return self::$_instance;
    }

    /**
     * Set the default ACL instance to a specified instance.
     *
     * @param PPI_Acl $acl An object instance of type PPI_Acl
     * @return void
     * @throws PPI_Exception if ACL is already initialized.
     */
    public static function setInstance(PPI_Acl $acl) {
        if (self::$_instance !== null) {
            throw new PPI_Exception('ACL is already initialized');
        }
        self::$_instance = $acl;
    }

    /**
     * Initialize the default ACL instance.
     *
     * @return void
     */
    protected static function init() {
        self::setInstance(new PPI_Acl());
    }
	
    /**
     * Obtain all the set rules
     *
     */
    function getRules() {
    	return $this->_rules;	
    }
    
    /**
     * Set all the rules
     *
     */
	function setRules(array $p_aRules) {
    	$this->_rules = $p_aRules;
    }
    
    /**
     * Check wether a user has access to a resource
     *
     */
    function hasAccess($p_sController = false, $p_sMethod = false, $p_sRole = false, $p_bThrow = false) {
    	if($p_sController === false ) {
			$p_sController = strtolower(PPI_Dispatch::getInstance()->getControllerName());	    	
    	}
    	if($p_sMethod === false) {
			$sMethodName = PPI_Model_Input::getInstance()->get(strtolower($p_sController));
    		$p_sMethod = $sMethodName ==  '' ? 'index' : $sMethodName;					    		
    	}
    	if($p_sRole === false) {
    		$p_sRole = getRoleType();
    	}
    	$aRules = $this->getRules();
    	if(array_key_exists($p_sController, $aRules)) {
    		$aRule = $aRules[$p_sController];
    		// Look for a direct roletype match
    		if(array_key_exists($p_sRole, $aRule['roles'])) {
    			if($aRule['roles'][$p_sRole] == 'allow') {
    				return true;
    			}
    			// No match do lets try to find a match through the inheritence chain	
    		} else {
    			// Go through the roles and if we find a greater ALLOW then we return true
    			$iRoleID = getRoleID();
    			foreach($aRule['roles'] as $sRoleName => $sAccessType) {
    				if(getRoleIDFromName($sRoleName) > $iRoleID) {
						return $sAccessType == 'deny' ? false : true;    					
    				}
    			}    			
    		}
    	}
    	if($p_bThrow === true) {
			throw new PPI_Exception("Access denied for user: $p_sRole to resource $p_sController/$p_sMethod");    		
    	}
    	return false;
    }
    
    /**
     * Give a user access to a resource. This is only temporary in memory.
     *
     */
    function setAccess() {
    	
    }
    
    /**
     * Remove user access to a resource. This is only temporary in memory.
     *
     */
    function removeAccess() {
    	
    }
    
	
}
