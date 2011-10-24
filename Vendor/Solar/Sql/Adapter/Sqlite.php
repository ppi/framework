<?php
/**
 * 
 * Class for connecting to SQLite (version 3) databases.
 * 
 * @category Solar
 * 
 * @package Solar_Sql
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Sqlite.php 4416 2010-02-23 19:52:43Z pmjones $
 * 
 */
class Solar_Sql_Adapter_Sqlite extends Solar_Sql_Adapter
{
    /**
     * 
     * Map of Solar generic types to RDBMS native types used when creating
     * portable tables.
     * 
     * @var array
     * 
     */
    protected $_solar_native = array(
        'bool'      => 'BOOLEAN',
        'char'      => 'CHAR',
        'varchar'   => 'VARCHAR',
        'smallint'  => 'SMALLINT',
        'int'       => 'INTEGER',
        'bigint'    => 'BIGINT',
        'numeric'   => 'NUMERIC',
        'float'     => 'DOUBLE',
        'clob'      => 'CLOB',
        'date'      => 'DATE',
        'time'      => 'TIME',
        'timestamp' => 'TIMESTAMP'
    );
    
    /**
     * 
     * Map of native RDBMS types to Solar generic types used when reading 
     * table column information.
     * 
     * @var array
     * 
     * @see fetchTableCols()
     * 
     */
    protected $_native_solar = array(
        'BOOLEAN'   => 'bool',
        'BOOL'      => 'bool',
        'CHAR'      => 'char',
        'VARCHAR'   => 'varchar',
        'SMALLINT'  => 'smallint',
        'INTEGER'   => 'int',
        'INT'       => 'int',
        'BIGINT'    => 'bigint',
        'NUMERIC'   => 'numeric',
        'DOUBLE'    => 'float',
        'FLOAT'     => 'float',
        'REAL'      => 'float',
        'CLOB'      => 'clob',
        'DATE'      => 'date',
        'TIME'      => 'time',
        'TIMESTAMP' => 'timestamp',
        'DATETIME'  => 'timestamp',
    );
    
    /**
     * 
     * The PDO adapter type.
     * 
     * @var string
     * 
     */
    protected $_pdo_type = 'sqlite';
    
    /**
     * 
     * The quote character before an entity name (table, index, etc).
     * 
     * @var string
     * 
     */
    protected $_ident_quote_prefix = '"';
    
    /**
     * 
     * The quote character after an entity name (table, index, etc).
     * 
     * @var string
     * 
     */
    protected $_ident_quote_suffix = '"';
    
    /**
     * 
     * The string used for Sqlite autoincrement data types.
     * 
     * This is different for versions 2 and 3 of SQLite.
     * 
     * @var string
     * 
     */
    protected $_sqlite_autoinc = 'INTEGER PRIMARY KEY AUTOINCREMENT';
    
    /**
     * 
     * Creates a PDO-style DSN.
     * 
     * For example, "mysql:host=127.0.0.1;dbname=test"
     * 
     * @param array $info An array with host, post, name, etc. keys.
     * 
     * @return string The DSN string.
     * 
     */
    protected function _buildDsn($info)
    {
        $dsn = array();
        if (! empty($info['name'])) {
            $dsn[] = $info['name'];
        }
        return $this->_pdo_type . ':' . implode(';', $dsn);
    }
    
    /**
     * 
     * After connection, set various connection attributes.
     * 
     * @return void
     * 
     */
    protected function _postConnect()
    {
        parent::_postConnect();
        $this->query("PRAGMA encoding = 'UTF-8';");
        
        // // these don't actually work in 3.x yet :-/
        // $this->query("PRAGMA short_column_names = 1;");
        // $this->query("PRAGMA full_column_names = 0;");
    }
    
    /**
     * 
     * Safely quotes a value for an SQL statement; unlike the main adapter,
     * the SQLite adapter **does not** quote numeric values.
     * 
     * If an array is passed as the value, the array values are quoted
     * and then returned as a comma-separated string; this is useful 
     * for generating IN() lists.
     * 
     * {{code: php
     *     $sql = Solar::factory('Solar_Sql');
     *     
     *     $safe = $sql->quote('foo"bar"');
     *     // $safe == "'foo\"bar\"'"
     *     
     *     $safe = $sql->quote(array('one', 'two', 'three'));
     *     // $safe == "'one', 'two', 'three'"
     * }}
     * 
     * @param mixed $val The value to quote.
     * 
     * @return string An SQL-safe quoted value (or a string of 
     * separated-and-quoted values).
     * 
     */
    public function quote($val)
    {
        if (is_numeric($val)) {
            return $val;
        } else {
            return parent::quote($val);
        }
    }
    
    /**
     * 
     * Returns a list of all tables in the database.
     * 
     * @param string $schema Fetch tbe list of tables in this attached
     * database; when empty, uses the main database.
     * 
     * @return array All table names in the database.
     * 
     */
    protected function _fetchTableList($schema)
    {
        if ($schema) {
            $cmd = "
                SELECT name FROM {$schema}.sqlite_master WHERE type = 'table'
                ORDER BY name
            ";
        } else {
            $cmd = "
                SELECT name FROM sqlite_master WHERE type = 'table'
                UNION ALL
                SELECT name FROM sqlite_temp_master WHERE type = 'table'
                ORDER BY name
            ";
        }
        
        return $this->fetchCol($cmd);
    }
    
    /**
     * 
     * Describes the columns in a table.
     * 
     * @param string $table The table name to fetch columns for.
     * 
     * @param string $schema The attached database in which the table resides.
     * 
     * @return array
     * 
     */
    protected function _fetchTableCols($table, $schema)
    {
        // sqlite> create table areas (id INTEGER PRIMARY KEY AUTOINCREMENT,
        //         name VARCHAR(32) NOT NULL);
        // sqlite> pragma table_info(areas);
        // cid |name |type        |notnull |dflt_value |pk
        // 0   |id   |INTEGER     |0       |           |1
        // 1   |name |VARCHAR(32) |99      |           |0
        
        // strip non-word characters to try and prevent SQL injections
        $table = preg_replace('/[^\w]/', '', $table);
        
        if ($schema) {
            // sanitize and add a dot
            $schema = preg_replace('/[^\w]/', '', $table) . '.';
        }
        
        // where the description will be stored
        $descr = array();
        
        // get the CREATE TABLE sql; need this for finding autoincrement cols
        $cmd = "
            SELECT sql FROM {$schema}sqlite_master
            WHERE type = 'table' AND name = :table
        ";
        $create_table = $this->fetchValue($cmd, array('table' => $table));
        
        // get the column descriptions
        $table = $this->quoteName($table);
        $cols = $this->fetchAll("PRAGMA {$schema}TABLE_INFO($table)");
        if (! $cols) {
            throw $this->_exception('ERR_NO_COLS_FOUND', array(
                'table' => $table,
                'schema' => substr($schema, -1), // drop the added dot
            ));
        }
        
        // loop through the result rows; each describes a column.
        foreach ($cols as $val) {
            $name = $val['name'];
            list($type, $size, $scope) = $this->_getTypeSizeScope($val['type']);
            
            // find autoincrement column in CREATE TABLE sql.
            $autoinc_find = str_replace(' ', '\s+', $this->_sqlite_autoinc);
            $find = "(\"$name\"|\'$name\'|`$name`|\[$name\]|\\b$name)" 
                  . "\s+$autoinc_find";
            
            $autoinc = preg_match(
                "/$find/Ui",
                $create_table,
                $matches
            );
            
            $default = null;
            if ($val['dflt_value'] && $val['dflt_value'] != 'NULL') {
                $default = trim($val['dflt_value'], "'");
            }
            
            $descr[$name] = array(
                'name'    => $name,
                'type'    => $type,
                'size'    => ($size  ? (int) $size  : null),
                'scope'   => ($scope ? (int) $scope : null),
                'default' => $default,
                'require' => (bool) ($val['notnull']),
                'primary' => (bool) ($val['pk'] == 1),
                'autoinc' => (bool) $autoinc,
            );
        }
        
        // For defaults using keywords, SQLite always reports the keyword
        // *value*, not the keyword itself (e.g., '2007-03-07' instead of
        // 'CURRENT_DATE').
        // 
        // The allowed keywords are CURRENT_DATE, CURRENT_TIME, and
        // CURRENT_TIMESTAMP.
        // 
        //   <http://www.sqlite.org/lang_createtable.html>
        // 
        // Check the table-creation SQL for the default value to see if it's
        // a keyword and report 'null' in those cases.
        
        // get the list of columns
        $cols = array_keys($descr);
        
        // how many are there?
        $last = count($cols) - 1;
        
        // loop through each column and find out if its default is a keyword
        foreach ($cols as $curr => $name) {
            
            // if there's no default value, there can't be a keyword.
            if (! $descr[$name]['default']) {
                continue;
            }
            
            // look for :curr_col :curr_type . DEFAULT CURRENT_(*)
            $find = $descr[$name]['name'] . '\s+'
                  . $this->_solar_native[$descr[$name]['type']]
                  . '.*\s+DEFAULT\s+CURRENT_';
            
            // if not at the end, don't look further than the next coldef
            if ($curr < $last) {
                $next = $cols[$curr + 1];
                $find .= '.*' . $descr[$next]['name'] . '\s+'
                       . $this->_solar_native[$descr[$next]['type']];
            }
            
            // is the default a keyword?
            preg_match("/$find/ims", $create_table, $matches);
            if (! empty($matches)) {
                $descr[$name]['default'] = null;
            }
        }
        
        // done!
        return $descr;
    }
    
    /**
     * 
     * Returns an array of index information for a table.
     * 
     * @param string $table The table name to fetch indexes for.
     * 
     * @param string $schema The attached database in which the table resides.
     * 
     * @return array An array of table indexes.
     * 
     */
    protected function _fetchIndexInfo($table, $schema)
    {
        // sqlite> PRAGMA INDEX_LIST('nodes');
        // seq|name|unique
        // 0|nodes__email|0
        // 1|nodes__area_id__name|1
        // 2|nodes__id|1
        //
        // sqlite> PRAGMA INDEX_INFO('nodes__area_id__name');
        // seqno|cid|name
        // 0|3|area_id
        // 1|5|name
        
        // strip non-word characters to try and prevent SQL injections
        $table = preg_replace('/[^\w]/', '', $table);
        
        if ($schema) {
            // sanitize and add a dot
            $schema = preg_replace('/[^\w]/', '', $table) . '.';
        }
        
        // where the index info will be stored
        $info = array();
        
        // get all indexes on the table
        $tmp = $this->_quoteName($table);
        $list = $this->fetchAll("PRAGMA {$schema}INDEX_LIST($tmp)");
        
        if (! $list) {
            // no indexes
            return array();
        }
        
        // table prefix string
        $pre = "{$table}__";
        $len = strlen($pre);
        
        // collect cols into indexes
        foreach ($list as $item) {
            
            // index name?
            $name = $item['name'];
            
            // strip table prefix?
            if (substr($name, 0, $len) == $pre) {
                $name = substr($name, $len);
            }
            
            // unique?
            $info[$name]['unique'] = (bool) $item['unique'];
            
            // what cols in the index?
            $tmp = $this->_quoteName($item['name']);
            $cols = $this->fetchAll("PRAGMA {$schema}INDEX_INFO($tmp)");
            
            // collect column info
            foreach ($cols as $col) {
                $info[$name]['cols'][] = $col['name'];
            }
        }
        
        // done!
        return $info;
    }
    
    /**
     * 
     * Creates a sequence, optionally starting at a certain number.
     * 
     * @param string $name The sequence name to create.
     * 
     * @param int $start The first sequence number to return.
     * 
     * @return void
     * 
     */
    protected function _createSequence($name, $start = 1)
    {
        $start -= 1;
        $name = $this->quoteName($name);
        $this->query("CREATE TABLE $name (id INTEGER PRIMARY KEY)");
        return $this->query("INSERT INTO $name (id) VALUES ($start)");
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
        $name = $this->quoteName($name);
        return $this->query("DROP TABLE IF EXISTS $name");
    }
    
    /**
     * 
     * Drops an index.
     * 
     * @param string $table The table of the index.
     * 
     * @param string $name The full index name.
     * 
     * @return void
     * 
     */
    protected function _dropIndex($table, $name)
    {
        $name = $this->quoteName($name);
        return $this->query("DROP INDEX $name");
    }
    
    /**
     * 
     * Modifies the index name.
     * 
     * SQLite won't allow two indexes of the same name, even if they are
     * on different tables.  This method modifies the name by prefixing with
     * the table name and two underscores.  Thus, for a index named 'bar' on 
     * a table named 'foo', the modified name will be 'foo__bar'.
     * 
     * @param string $table The table on which the index occurs.
     * 
     * @param string $name The requested index name.
     * 
     * @return string The modified index name.
     * 
     */
    protected function _modIndexName($table, $name)
    {
        return $table . '__' . $name;
    }
    
    /**
     * 
     * Gets a sequence number; creates the sequence if it does not exist.
     * 
     * @param string $name The sequence name.
     * 
     * @return int The next sequence number.
     * 
     */
    protected function _nextSequence($name)
    {
        $cmd = "INSERT INTO " . $this->quoteName($name)
             . " (id) VALUES (NULL)";
        
        // first, try to increment the sequence number, assuming
        // the table exists.
        try {
            $this->query($cmd);
        } catch (Exception $e) {
            // error when updating the sequence.
            // assume we need to create it, then
            // try to increment again.
            $this->_createSequence($name);
            $this->query($cmd);
        }
        
        // get the sequence number
        return $this->_pdo->lastInsertId();
    }
    
    /**
     * 
     * Given a column definition, modifies the auto-increment and primary-key
     * clauses in place.
     * 
     * @param string &$coldef The column definition as it is now.
     * 
     * @param bool $autoinc Whether or not this is an auto-increment column.
     * 
     * @param bool $primary Whether or not this is a primary-key column.
     * 
     * @return void
     * 
     */
    protected function _modAutoincPrimary(&$coldef, $autoinc, $primary)
    {
        if ($autoinc) {
            // forces datatype, primary key, and autoincrement
            $coldef = $this->_sqlite_autoinc;
        } elseif ($primary) {
            $coldef .= ' PRIMARY KEY';
        }
    }
}
