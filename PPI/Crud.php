<?php
/**
 * @author    Paul Dragoonis <dragoonis@php.net>
 * @license   http://opensource.org/licenses/mit-license.php MIT
 * @package   CRUD
 */
class PPI_Crud {
	/**
	 * This function is used to parse/load a yaml file.
	 *
	 * @param $file
	 * @return yaml parsed
	 */
	function getFormStructure($file) {
		$file = CONFIGPATH.'Forms/'.$file.".yaml";
		if(extension_loaded('yaml')) {
			// do stuff here -- with yaml
			return yaml_parse_file($file);
		} else {
			// use spyc
			return Spyc::YAMLLoad($file);
		}
	}
}