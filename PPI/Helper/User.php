<?php
/**
 * @author    Paul Dragoonis <dragoonis@php.net>
 * @license   http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package   Helper
 * @package   www.ppiframework.com
 */
namespace PPI\Helper;
use PPI\Core,
	PPI\Core\CoreException;

class User {


    /**
     * Convert a users RoleID to their RoleName
     *
     * @static
     * @throws CoreException
     * @param  integer $p_iRoleID
     * @return string
     */
	static function getRoleNameFromID($p_iRoleID) {
		$aRoles = array_flip(self::getRoles());
		if(array_key_exists($p_iRoleID, $aRoles)) {
			return $aRoles[$p_iRoleID];
		}
		throw new CoreException('Unknown Role ID: '.$p_iRoleID);		
	}

    /**
     * Convert a role name to a 'nice' role name which makes it UI friendly.
     *
     * @static
     * @param  string $sRoleName
     * @return string
     */
	static function getRoleNameNice($sRoleName) {
		return ucwords(str_replace('_', ' ', $sRoleName));
	}
		
	/**
	 * Returns an array of role_id => role_type of all the roles defined
	 *
     * @return array
	 */
	static function getRoles() {
		$oConfig = Core::getConfig();
		if(isset($oConfig->user->roleMappingService) && $oConfig->user->roleMappingService == 'database') {
			$oUser     = new APP\Model\User\Role();
			$aRoles    = $oUser->getList()->fetchAll();
			$aRetRoles = array();
			foreach($aRoles as $aRole) {
				$aRetRoles[$aRole['name']] = $aRole['id'];
			}
			return $aRetRoles;
		} else {
			return $oConfig->system->roleMapping->toArray();	
		}
	}	
	
}