<?php
/**
 * 
 * Factory class for SQL connections.
 * 
 * @category Solar
 * 
 * @package Solar_Sql Adapters for SQL database interaction, portable data
 * definition, and table metadata.
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Sql.php 4380 2010-02-14 16:06:52Z pmjones $
 * 
 */
class Solar_Sql extends Solar_Factory
{
    /**
     * 
     * Default configuration values.
     * 
     * @config string adapter The class to factory, for example 'Solar_Sql_Adapter_Mysql'.
     * 
     * @var array
     * 
     */
    protected $_Solar_Sql = array(
        'adapter' => 'Solar_Sql_Adapter_Sqlite',
    );
}
