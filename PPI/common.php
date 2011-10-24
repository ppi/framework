<?php

defined('PPI_CONTROLLER')			|| define('PPI_CONTROLLER', 0);
defined('PPI_CONTROLLER_PLUGIN')	|| define('PPI_CONTROLLER_PLUGIN', 1);

/**
 * This function returns the role name of the user
 * @returns string
 */
function getRoleType() {

	$aUserInfo = PPI_Session::getInstance()->getAuthData();
	return false !== $aUserInfo && array() !== $aUserInfo ? $aUserInfo['role_name'] : 'guest';
}

/**
 * This function returns the role number of the user
 * @todo Do a lookup for the guest user ID instead of defaulting to 1
 * @return integer
 */
function getRoleID() {

	$aUserInfo = PPI_Session::getInstance()->getAuthData();
	return false !== $aUserInfo && array() !== $aUserInfo ? $aUserInfo['role_id'] : 1;
}

function getRoleIDFromName($p_sRoleName) {

	$aRoles = PPI_Helper::getConfig()->system->roleMapping->toArray();
	if (isset($aRoles[$p_sRoleName])) {
		return $aRoles[$p_sRoleName];
	}
	throw new PPI_Exception('Unknown Role Type: ' . $p_sRoleName);
}

/**
 * Returns an array of role_id => role_type of all the roles defined
 *
 */
function getRoles() {

	return PPI_Helper::getConfig()->system->roleMapping->toArray();
}

function roleNameToID($p_sName) {

	if (!isset(PPI_Helper::getConfig()->system->roleMapping)) {
		throw new PPI_Exception('Trying to perform roleIDToName when no roleMapping information was found.');
	}

	$aRoles = getRoles();
	if (!isset($aRoles[$p_sName])) {
		throw new PPI_Exception('Unable to find role: ' . $p_sName);
	}
	return $aRoles[$p_sName];
}

function roleIDToName($p_iID) {

	if (!isset(PPI_Helper::getConfig()->system->roleMapping)) {
		throw new PPI_Exception('Trying to perform roleIDToName when no roleMapping information was found.');
	}
}

function print_rn($aData='') {
	echo '<pre>';
	print_r($aData);
	echo '</pre>';
}

/**
 * Returns an array of all the controllers filenames that could potentially be accessed from the URL
 * @return array
 */
function getControllerList() {
	$aControllers = array();
	foreach (array(PLUGINCONTROLLERPATH, CONTROLLERPATH) as $sCurrPath) {
		if ($handle = opendir($sCurrPath)) {
			while (false !== ($file = readdir($handle))) {
				if ($file != "." && $file != ".." && strpos($file, '.php') !== false) {
					if (!in_array(strtolower($file), $aControllers)) {
						$aControllers[] .= str_replace('.php', '', strtolower($file));
					}
				}
			}
			closedir($handle);
		}
	}
	return $aControllers;
}

/**
 * Check the MySQL version to see if it's too low or not.
 * @return boolean
 */
function db_check_version() {

	$points = explode('.', mysql_get_server_info());
	return $points[0] < 5;
}

/**
 * Check that the installed version of PHP meets the minimum requirements (currently 5.2 or greater).
 * @return boolean
 */
function php_check_version() {
	return (version_compare(phpversion(), '5.1.2', '>='));
}

/**
 * Check if the database has been installed
 */
function is_db_installed() {

	$config = PPI_Registry::get('config');
	$oUser	= new PPI_Model_User();
	$rows	= $oUser->query('SELECT ' . $config->system->defaultUserTable . ' FROM ' . $config->system->defaultUserTable . ' LIMIT 1');
	return array() !== $rows;
}

/**
 * Check if a variable is set and not empty and return a default if false
 * @param <type> $var
 * @param <type> $alt
 * @return <type>
 */
function ifset(&$var, $alt=null) {

	if (empty($var)) {
		return $alt;
	}
	return $var;
}

/**
 * This function works like phpinfo(); and returns a html representation of the Framework settings.
 */
function ppi_info() {

	global $oConfig;
	$sVersion	= PPI_VERSION;
	$sBasePath	= BASEPATH;
	$sViewPath	= defined('PLUGINVIEWPATH') ? PLUGINVIEWPATH : VIEWPATH;
	$sModelPath	= MODELPATH;

	$sBaseUrl			= $oConfig->system->base_url;
	$sMaintenanceMode	= ($oConfig->system->maintenance == true) ? 'On' : 'Off';
	$sAutoloadplugins	= ($oConfig->system->autoload_plugins == true) ? 'On' : 'Off';
	$sAllowedHosts		= implode(' / ', $oConfig->system->allow_access->toArray());
	$sSessionName		= $oConfig->system->session_name;
	$sPluginPath		= PLUGINPATH;
	$sPluginViewPath	= PLUGINVIEWPATH;
	$sPluginModelPath	= PLUGINMODELPATH;
	$sPluginControllerPath = PLUGINCONTROLLERPATH;
	$sSiteType			= getSiteType();

	$sHtml = <<<EOF

      <style>
        .ppi_info
	{
	 /*  background-color:#2B2B2B; */
	   border: 1px thin black;

	   color: black;
	   font-family: "Lucida Grande", Lucida, Verdana, sans-serif;
	   text-shadow: #fff 0px 0px 0px; font-size:12px;
	   line-height:1.9em;
	}

      </style>

      <table width="600" cellpadding="3" border="0" align="center" class="ppi_info">
       <tbody>
         <tr>
	 <td colspan="2">
	    <h1>PPI framework version {$sVersion}</h1>
	 </td>
       </tr>
       <tr>
         <td colspan="2">
	    <br />
	 </td>
       </tr>
       <tr>
         <td>Base url: </td>
	 <td>{$sBaseUrl}</td>
       </tr>
       <tr>
         <td>Site type:</td>
	 ``<td>{$sSiteType}</td>
       </tr>
       <tr>
         <td>Default DB Fetch Mode</td>
	 ``<td>{$oConfig->db->mysql_default_fetch}</td>
       </tr>
       <tr>
         <td>Maintenance mode: </td>
	 <td>{$sMaintenanceMode}</td>
       </tr>
      <tr>
         <td>Allowed hosts:<br />
	 <small>(if in Maintenance mode)</small>
	 </td>
	 <td><strong>{$sAllowedHosts}</strong></td>
       </tr>
        <tr>
         <td>Ppi session name: </td>
	 <td>{$oConfig->system->session_namespace}</td>
       </tr>
      <tr>
         <td>Autoload plugin mode: </td>
	 <td>{$sAutoloadplugins}</td>
	 </tr>

       <tr>
         <td>Base path: </td>
	 <td>{$sBasePath}</td>
       </tr>
       <tr>
         <td>View path: </td>
	 <td>{$sViewPath}</td>
       </tr>
       <tr>
         <td>Model path: </td>
	 <td>{$sModelPath}</td>
       </tr>
       <tr>
         <td>View path: </td>
	 <td>{$sViewPath}</td>
       </tr>

       <tr>
         <td>Plugin path: </td>
	 <td>{$sPluginPath}</td>
       </tr>
        <tr>
         <td>Plugin Controller path: </td>
	 <td>{$sPluginControllerPath}</td>
       </tr>
        <tr>
         <td>Plugin Model path: </td>
	 <td>{$sPluginModelPath}</td>
       </tr>
        <tr>
         <td>Plugin View path: </td>
	 <td>{$sPluginViewPath}</td>
       </tr>
      </tbody>
   </table>
EOF;
	echo $sHtml;
	// phpinfo();
}

function getHTTPHostname() {
	return str_replace('www.', '', $_SERVER['HTTP_HOST']);
}

function getSiteType() {
	global $siteTypes;

	$sHostName = getHTTPHostname();
	foreach ($siteTypes as $key => $val) {
		if ($key == $sHostName) {
			return $val;
		}
	}
	return 'unknown';
}

/**
 * Dumping a variable with backtracing information
 * @param mixed $var
 */
function ppi_dump($var, $p_bThrowException=false) {

	if (false === $p_bThrowException) {

		$trace = debug_backtrace();
		$label = 'File: ' . str_replace($_SERVER['DOCUMENT_ROOT'], '...', $trace[0]['file'] . "<br>\n");
		$label.= 'Line: ' . $trace[0]['line'] . "<br>\n";

		if (isset($trace[1])) {
			$label.= 'Function: ' . $trace[1]['function'] . "<br>\n";
		} else {
			$label.= "Global (not in function)<br>\n";
		}
		echo $label . '<pre>';
		var_dump($var);
		echo '</pre>';
	} else {
		echo '<pre>';
		var_dump($var);
		throw new PPI_Exception('Exceptioned Dump');
	}
}