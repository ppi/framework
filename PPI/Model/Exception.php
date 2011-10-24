<?php
/**
 *
 * @version   1.0
 * @author    Paul Dragoonis <dragoonis@php.net>
 * @license   http://opensource.org/licenses/mit-license.php MIT
 * @copyright Digiflex Development
 * @package   Model
 * @link      www.ppiframework.com
 */
class PPI_Model_Exception extends PPI_Model
{

	public function __construct ()
	{
		parent::__construct('ppi_errors', 'id');
	}

	/**
	 * Delete an exception fromt he lgo
	 * @param unknown_type $p_iRecordID
	 */
	public function deleteRecord($p_iRecordID="")
	{
		if (empty ($p_iRecordID))
		 return false;

		$sQuery = "
			DELETE FROM
			ppi_errors
			WHERE
			id='".mysql_real_escape_string($p_iRecordID)."'";
		return  $this->query ($sQuery, __FUNCTION__);
	}
}
