<?php
/**
 * 
 * Represents the characteristics of a relationship where a native model
 * "has one or none" of a foreign model; the difference from "has one" is
 * is that when there is no related at the database, no placeholder record
 * will be returned.
 * 
 * @category Solar
 * 
 * @package Solar_Sql_Model
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: HasOneOrNull.php 4376 2010-02-11 23:13:07Z pmjones $
 * 
 */
class Solar_Sql_Model_Related_HasOneOrNull extends Solar_Sql_Model_Related_HasOne
{
    /**
     * 
     * Returns a null when there is no related data.
     * 
     * @return null
     * 
     */
    public function fetchEmpty()
    {
        return null;
    }
}
