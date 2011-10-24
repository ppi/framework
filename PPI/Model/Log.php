<?php
/**
*
* @author    Paul Dragoonis <dragoonis@php.net>
* @license   http://opensource.org/licenses/mit-license.php MIT
* @package   Model
*/
namespace PPI\Model;
class Log {

	function addExceptionLog(array $p_aError) {
		$oDB = new PPI\Model\Shared('ppi_exception', 'id');
		$oDB->putRecord($p_aError);
	}

	function addErrorLog(array $p_aError) {
		$oDB = new PPI\Model\Shared('ppi_errors', 'id');
		$oDB->putRecord($p_aError);
	}

	function addEmailLog(array $p_aError) {
		$oDB = new PPI\Model\Shared('ppi_email_log', 'id');
		$oDB->putRecord($p_aError);
	}

	/**
	 * Get email logs with an optional clause
	 *
	 * @param string array $p_mWhere
	 */
	function getEmailLogs($p_mWhere = '') {
		$oDB = new PPI\Model\Shared('ppi_email_log', 'id');
		return $oDB->getList($p_mWhere);
	}

	function addSystemLog(array $p_aError) {
		$oDB = new PPI\Model\Shared('ppi_system_log', 'id');
		$oDB->putRecord($p_aError);
	}
	/**
	 * Get system logs with an optional clause
	 *
	 * @param string array $p_mWhere
	 */
	function getSystemLogs($p_mWhere = '') {
		$oDB = new PPI\Model\Shared('ppi_system_log', 'id');
		return $oDB->getList($p_mWhere, 'created desc');
	}
}
