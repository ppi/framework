<?php
/**
 * 
 * Exception: query failed for some reason.
 * 
 * Generally thrown in place of a PDOException; it serves the same purpose,
 * but adds some more info about the failure.
 * 
 * @category Solar
 * 
 * @package Solar_Sql
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: QueryFailed.php 2933 2007-11-09 20:37:35Z moraes $
 * 
 */
class Solar_Sql_Adapter_Exception_QueryFailed extends Solar_Sql_Adapter_Exception {}
