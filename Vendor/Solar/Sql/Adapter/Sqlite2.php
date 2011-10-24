<?php
/**
 * 
 * Class for connecting to SQLite (version 2) databases.
 * 
 * @category Solar
 * 
 * @package Solar_Sql
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Sqlite2.php 3563 2008-11-06 03:04:52Z pmjones $
 * 
 */
class Solar_Sql_Adapter_Sqlite2 extends Solar_Sql_Adapter_Sqlite
{
    /**
     * 
     * The PDO adapter type.
     * 
     * @var string
     * 
     */
    protected $_pdo_type = 'sqlite2';
    
    /**
     * 
     * The string used for Sqlite autoincrement data types.
     * 
     * This is different for versions 2 and 3 of SQLite.
     * 
     * @var string
     * 
     */
    protected $_sqlite_autoinc = 'INTEGER AUTOINCREMENT PRIMARY KEY';
    
    /**
     * 
     * Drops a table from the database, if it exists.
     * 
     * @param string $table The table name.
     * 
     * @return mixed
     * 
     */
    public function dropTable($table)
    {
        // get a fresh list of tables
        $this->_cache->deleteAll();
        $list = $this->fetchTableList();
        
        // does the table exist?
        if (in_array($table, $list)) {
            // kill the cache again, then drop the table
            $this->_cache->deleteAll();
            $table = $this->quoteName($table);
            return $this->query("DROP TABLE $table");
        }
    }
    
    /**
     * 
     * Drops a sequence.
     * 
     * @param string $name The sequence name to drop.
     * 
     * @return void
     * 
     */
    protected function _dropSequence($name)
    {
        return $this->dropTable($name);
    }
}