<?php
/**
 * 
 * Retains metadata about a model table.
 * 
 * @category Solar
 * 
 * @package Solar_Sql_Model
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Metadata.php 4376 2010-02-11 23:13:07Z pmjones $
 * 
 */
abstract class Solar_Sql_Model_Metadata extends Solar_Base
{
    /**
     * 
     * The name of the table.
     * 
     * @var string
     * 
     */
    public $table_name = null;
    
    /**
     * 
     * Column descriptions for the table.
     * 
     * @var array
     * 
     * @see Solar_Sql_Adapter::fetchTableCols()
     * 
     */
    public $table_cols = array();
    
    /**
     * 
     * Index information for the table.
     * 
     * @var array
     * 
     * @see Solar_Sql_Adapter::fetchIndexInfo()
     * 
     */
    public $index_info = array();
}