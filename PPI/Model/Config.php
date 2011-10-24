<?php
/**
 *
 * @version   1.0
 * @author    Paul Dragoonis <dragoonis@php.net>
 * @license   http://opensource.org/licenses/mit-license.php MIT
 * @copyright Digiflex Development
 * @package   Config
 */

class PPI_Model_Config {

	/**
	 * Has the config been read or not ?
	 *
	 * @var boolean
	 */
	protected $bRead       = null;

	/**
	 * The config object
	 *
	 * @var PPI_Config
	 */
	protected $_oConfig    = null;

	/**
	 * The config file that was parsed
	 *
	 * @var string
	 */
	protected $_configFile = null;

	function __construct() {}

	/**
	 * Actually go and get the config parse it and store it for later retreival
	 *
	 * @param null $p_sConfigFile
	 * @return PPI_Config
	 */
	function getConfig($p_sConfigFile = null) {
		$this->_configFile = $p_sConfigFile !== null ? $p_sConfigFile : 'general.ini';
		if ($this->bRead === null) {
			$this->readConfig();
			$this->bRead = true;
		}
		return $this->_oConfig;
	}

	/**
	 * Read the config file, only ini type implemented
	 *
	 * @todo Implement XML and PHP config files
	 * @return void
	 */
	function readConfig () {
		global $siteTypes;
		if(!file_exists(CONFIGPATH . $this->_configFile)) {
			die('Unable to find '. $this->_configFile .' file, please check your application configuration');
		}
		$ext = PPI_Helper::getFileExtension($this->_configFile);
		$sHostname = getHTTPHostname();
		$siteType = array_key_exists($sHostname, $siteTypes) ? $siteTypes[$sHostname] : 'development';
		switch($ext) {
			case 'ini':
				$this->_oConfig = new PPI_Config_Ini(parse_ini_file(CONFIGPATH . $this->_configFile, true), $siteType);
				break;

			case 'xml':
				die('Trying to load a xml config file but no parser yet created.');
				break;

			case 'php':
				die('Trying to load a php config file but no parser yet created.');
				break;

		}
	}

	/**
	 * Converts role name to role ID
	 *
	 * @param integer $p_roleID The Role ID
	 * @return string
	 */
	function getRoleNameFromID($p_roleID) {
		foreach($this->roleMapping as $key => $val) {
			if($val == $p_roleID) {
				return $key;
			}
		}
		return '';
	}

	/**
	 * Get the Role ID from the Role Name
	 * 
	 * @param string $p_roleName Role Name
	 * @return integer
	 */
	function getRoleIDFromName($p_roleName) {
		foreach($this->_oConfig->system->roleMapping as $key => $val) {
			if($key == $p_roleName) {
				return $val;
			}
		}
		return 0;
	}
}
