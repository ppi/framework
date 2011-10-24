<?php
/**
 * @author    Paul Dragoonis <dragoonis@php.net>
 * @license   http://opensource.org/licenses/mit-license.php MIT
 * @copyright Digiflex Development
 * @package   Model
 * @link      www.ppiframework.com
 */

class PPI_Model_Schema extends PPI_Model {
    protected $_primary = '';
    protected $_name = '';

    function __construct($tableName, $primaryKey) {
        $this->_name = $tableName;
        $this->_primary = $primaryKey;
		parent::__construct($this->_name, $this->_primary);
	}

    /**
	 * This function is used to get the columns of a given table.
	 *
	 * @param $tableName
	 * @return array with columns.
	 */
    function getTableColumns($tableName = null) {
        $sql = "SHOW COLUMNS FROM ";
        if (isset($tableName)) {
            $sql.=$tableName;
        } else {
            $sql.=$this->_name;
        }

        $cols = array();

        foreach($this->query($sql) as $row) {
            $cols[] = $row;
        }

        return $cols;
    }

	/**
	 * Take the table schema and convert that into a .yaml file
	 *
	 * @param string $file
	 * @return void
	 */
    function createYAML($file) {
        require_once VENDORPATH."Spyc/Spyc.php";
        $cols = $this->getTableColumns();
        $file = CONFIGPATH.'Forms/'.$file.".yaml";
        if(extension_loaded('yaml')) {
			// do stuff here -- with yaml
			$yaml = yaml_emit($cols);
		} else {
			// use spyc
			$yaml = Spyc::YAMLDump($cols);
		}

        file_put_contents($file,$yaml);
    }
}