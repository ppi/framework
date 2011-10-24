<?php
/**
 * 
 * Class for Oracle (OCI) behaviors.
 * 
 * Many thanks to James Kilbride, Darwin Cruz, and others at General Dynamics
 * for this BSD-Licensed contribution.
 * 
 * Special note: make sure the connecting user has permission to issue an
 * `ALTER SESSION SET NLS_DATE_FORMAT` command; this makes the Oracle dates
 * ISO compliant.
 * 
 * @category Solar
 * 
 * @package Solar_Sql
 * 
 * @author James Kilbride <james.kilbride@gd-ais.com>
 * 
 * @copyright 2008 General Dynamics Advanced Information Systems
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Oracle.php 4416 2010-02-23 19:52:43Z pmjones $
 * 
 */
class Solar_Sql_Adapter_Oracle extends Solar_Sql_Adapter
{
    /**
     * 
     * Map of Solar generic types to RDBMS native types used when creating
     * portable tables. Oracle recognizes and uses ANSI standard datatypes
     * in table creation. It internally converts to Oracle types but for
     * simplicity where an ansi standard type was equivalent to Solar
     * we will use that ansi standard type and let Oracle do its own internal
     * conversion.
     * 
     * @var array
     * 
     */
    protected $_solar_native = array(
        'bool'      => 'INTEGER',
        'char'      => 'CHAR',
        'varchar'   => 'VARCHAR2',
        'smallint'  => 'INTEGER',
        'int'       => 'INTEGER',
        'bigint'    => 'INTEGER',
        'numeric'   => 'NUMERIC',
        'float'     => 'DOUBLE',
        'clob'      => 'CLOB',
        'date'      => 'DATE',
        'time'      => 'DATE',
        'timestamp' => 'DATE',
    );
    
    /**
     * 
     * Map of native RDBMS types to Solar generic types used when reading 
     * table column information.
     *
     * Some of the 'native' types listed are actually for the ANSI, SQL/DS
     * and DB2 types. They are listed because Oracle recognizes them as valid
     * types and will use them as the types on the table description if that
     * is what is used in the create statement. Internally Oracle converts
     * them to its own types but they will be reported to Solar as though
     * they were the ANSI or IBM types.
     * 
     * Note that fetchTableCols() will programmatically convert TINYINT(1) to
     * 'bool' independent of this map.
     * 
     * @var array
     * 
     * @see fetchTableCols()
     * 
     * @todo Need to update list for Oracles types. Native to Solar types.
     * 
     */
    protected $_native_solar = array(
        
        // numeric
        'smallint'                      => 'smallint',
        'int'                           => 'int',
        'integer'                       => 'int',
        'bigint'                        => 'bigint',
        'dec'                           => 'numeric',
        'decimal'                       => 'numeric',
        'double'                        => 'float',
        'number'                        => 'numeric',
        'binary_double'                 => 'float',
        'binary_float'                  => 'float',
        'double precision'              => 'float',
        'real'                          => 'float',
        
        // date & time
        'date'                          => 'timestamp',
        'timestamp'                     => 'timestamp',
        
        // string
        'national char'                 => 'char',
        'nchar'                         => 'char',
        'char'                          => 'char',
        'character'                     => 'char',
        'character varying'             => 'varchar',
        'char varying'                  => 'varchar',
        'national char'                 => 'varchar',
        'national varchar'              => 'varchar',
        'national character varying'    => 'varchar',
        'national char varying'         => 'varchar',
        'nchar varying'                 => 'varchar',
        'nvarchar'                      => 'varchar',
        'nvarchar2'                     => 'varchar',
        'varchar2'                      => 'varchar',
        
        // clob
        'clob'                          => 'clob',
        'nclob'                         => 'clob',
        'long varchar2'                 => 'clob',
        'long varchar'                  => 'clob',
        'long char'                     => 'clob',
        'long'                          => 'clob',
    );
    
    /**
     * 
     * The PDO adapter type.
     * 
     * @var string
     * 
     */
    protected $_pdo_type = 'oci';
 
    /**
     * 
     * Returns a list of all tables in the database.
     * 
     * @param string $schema Fetch tbe list of tables in this schema; 
     * when empty, uses the default schema.
     * 
     * @return array All table names in the database.
     * 
     */
    protected function _fetchTableList($schema) 
    {
         return $this->fetchCol('SELECT LOWER(TABLE_NAME) FROM USER_TABLES');
    }
    
    /**
     * 
     * Returns an array describing the columns in a table.
     * 
     * @param string $table The table name to fetch columns for.
     * 
     * @param string $schema The schema in which the table resides.
     * 
     * @return array An array of table column information.
     * 
     */
    protected function _fetchTableCols($table, $schema)
    {
        // strip non-word characters to try and prevent SQL injections
        $table = preg_replace('/[^\w]/', '', $table);
        
        // upper-case the table name for queries
        $table_name = strtoupper($table);
        
        // where the description will be stored
        $descr = array();
        
        // get the column info
        $stmt = "SELECT *
                 FROM USER_TAB_COLUMNS
                 WHERE TABLE_NAME = :table_name";
        
        $data = array('table_name' => $table_name);
        
        // get the column descriptions
        $cols = $this->fetchAll($stmt, $data);
        if (! $cols) {
            throw $this->_exception('ERR_NO_COLS_FOUND', array(
                'table' => $table,
                'schema' => $schema,
            ));
        }
        
        // loop through the result rows; each describes a column.
        foreach ($cols as $val) {
            
            $name = strtolower($val['column_name']);
            
            // override $type to find tinyint(1) as boolean
            $is_bool = strtolower($val['data_type']) == 'tinyint' &&
                       $val['data_length'] == 1;
                       
            if ($is_bool) {
                $type = 'bool';
                $size = null;
                $scope = null;
            } else {
                list($type, $size, $scope) = $this->_getTypeSizeScope($val['data_type']);
                $size = $val['data_length'];
                $scope = $val['data_precision'];
            }
            
            // save the column description
            $descr[$name] = array(
                'name'    => $name,
                'type'    => $type,
                'size'    => ($size  ? (int) $size  : null),
                'scope'   => ($scope ? (int) $scope : null),
                'default' => $this->_getDefault($val['data_default']),
                'require' => (bool) ($val['nullable'] != 'Y'),
                'primary' => false, // default, may change later
                'autoinc' => false, // default, may change later
            );
            
            // don't keep "size" for integers
            if (substr($type, -3) == 'int') {
                $descr[$name]['size'] = null;
            }
        }
        
        // To identify primary keys it is necessary to pull out the
        // constraints on the table. Loop through the constraints looking for
        // type P(primary) and then lookup the associated Column name.
        $stmt = "SELECT *
                 FROM USER_CONSTRAINTS
                 WHERE TABLE_NAME = :table_name";
        
        $data = array('table_name' => $table_name);
        
        $constraints = $this->fetchAll($stmt, $data);
        
        foreach ($constraints as $constraint) {
            if ($constraint['constraint_type'] == 'P') {
                
                $name = $constraint['constraint_name'];
                
                $stmt = "SELECT *
                         FROM USER_CONS_COLUMNS 
                         WHERE CONSTRAINT_NAME = :name";
                
                $primaryKey = $this->fetchOne($stmt, array('name' => $name));
                $primaryKey['column_name'] = strtolower($primaryKey['column_name']);
                $descr[$primaryKey['column_name']]['primary'] = true;
            }
        }
        
        // Need to pull triggers and see if you can find one that goes off on
        // the insert. This is complicated for identifying auto-increment
        // columns. May need to look for a sequence as well perhaps? Oracle
        // really doesn't have a way to automatically identify
        // autoincrementing columns unless it's going to be done via a
        // trigger and sequence maybe.
        $stmt = "SELECT TRIGGER_NAME
                 FROM USER_TRIGGERS
                 WHERE TABLE_NAME = :table_name
                 AND TRIGGERING_EVENT = :event
                 AND STATUS = :status";
        
        $data = array(
            'table_name' => $table_name,
            'event' => 'INSERT',
            'status' => 'ENABLED',
        );
        
        $triggers = $this->fetchAll($stmt, $data);
        
        foreach ($triggers as $trigger) {
            $compare = substr_compare(
                $trigger['trigger_name'],
                "IN_",
                0,
                strlen("IN_"),
                true
            );
            if ($compare == 0) {
                $col_name = substr(
                    $trigger['trigger_name'],
                    strlen("IN_")
                );
                foreach ($descr as $col_key => $col_desc) {
	                if (strcmp(strtoupper($col_name),strtoupper($col_key)) == 0) {
	                    $descr[$col_key]['autoinc'] = true;
	                }	                
                }
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
     * @param string $schema The schema in which the table resides.
     * 
     * @return array An array of table indexes.
     * 
     */
    protected function _fetchIndexInfo($table, $schema)
    {
        throw $this->_exception('ERR_METHOD_NOT_IMPLEMENTED', array(
            'method' => __FUNCTION__,
        ));
    }
    
    /**
     * 
     * Given a native column SQL default value, finds a PHP literal value.
     * 
     * SQL NULLs are converted to PHP nulls.  Non-literal values (such as
     * keywords and functions) are also returned as null.
     * 
     * @param string $default The column default SQL value.
     * 
     * @return scalar A literal PHP value.
     * 
     */
    protected function _getDefault($default)
    {
        $upper = strtoupper($default);
        if ($upper == 'NULL') {
            return null;
        } else {
            // return the literal default
            return $default;
        }
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
        $name = $this->quoteName(strtoupper($name));
        return $this->query("CREATE SEQUENCE $name START WITH $start");
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
        $name = $this->quoteName(strtoupper($name));
        return $this->query("DROP SEQUENCE IF EXISTS $name");
    }
    
    /**
     * 
     * Gets the next sequence number; creates the sequence if needed.
     * 
     * @param string $name The sequence name to increment.
     * 
     * @return int The next sequence number.
     * 
     */    
    protected function _nextSequence($name)
    {
        try {
            $cmd = "SELECT " . $this->quoteName(strtoupper($name))
                 . ".NEXTVAL FROM DUAL";
            
            $result = $this->query($cmd);
        } catch (Exception $e) {
            // error when trying to select the nextValue from the sequence.
            // assume we need to create it, then try to increment again.
            $this->_createSequence($name);
            $result = $this->query($cmd);
        }
        return $result;
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
        $name = $this->quoteName(strtoupper($name));
        return $this->query("DROP INDEX IF EXISTS $name");
    }
    
    /**
     * 
     * Given a column definition, modifies the auto-increment and primary-key
     * clauses in place. 
     * 
     * For Oracle it only modifies the primary key clause since autoincrement is
     * done via post table creation triggers.
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
        if ($primary) {
            $coldef .= " PRIMARY KEY";
        }
    }
    
    /**
     * 
     * Modifies a SELECT statement in place to add a LIMIT clause.
     * 
     * @param string &$stmt The SELECT statement.
     * 
     * @param array &$parts The orignal SELECT component parts, in case the
     * adapter needs them.
     * 
     * @return void
     * 
     * @todo Override to handle Oracle limitations.
     * 
     */
    protected function _modSelect(&$stmt, &$parts)
    {
        // determine count
        $count = ! empty($parts['limit']['count'])
               ? (int) $parts['limit']['count']
               : 0;
        
        // determine offset
        $offset = ! empty($parts['limit']['offset'])
                ? (int) $parts['limit']['offset']
                : 0;
        
        // add the count and offset
        if ($count > 0) {
            $max_rows = $count + $offset;
            $min_rows = $offset;
            $stmt = "SELECT * FROM (
                         SELECT A.*, ROWNUM RNUM
                         FROM ( $stmt ) A
                         WHERE rownum <= $max_rows
                     ) WHERE RNUM >= $min_rows";
        }
    }
    
    /**
     * 
     * Overrides the adapter's create Table to manage Oracle's specific needs
     * for table creation. Creates a portable table.
     * 
     * The $cols parameter should be in this format ...
     * 
     * {{code: php
     *     $cols = array(
     *       'col_1' => array(
     *         'type'    => (string) bool, char, int, ...
     *         'size'    => (int) total length for char|varchar|numeric
     *         'scope'   => (int) decimal places for numeric
     *         'default' => (bool) the default value, if any
     *         'require' => (bool) is the value required to be NOT NULL?
     *         'primary' => (bool) is this a primary key column?
     *         'autoinc' => (bool) is this an auto-increment column?
     *       ),
     *       'col_2' => array(...)
     *     );
     * }}
     * 
     * For available field types, see Solar_Sql_Adapter::$_native.
     * 
     * @param string $table The name of the table to create.
     * 
     * @param array $cols Array of columns to create.
     * 
     * @return string An SQL string.
     * 
     * @todo Instead of stacking errors, stack info, then throw in exception.
     * 
     */
    public function createTable($table, $cols)
    {
    	$table_name = strtoupper($table);
    	
        // main creation routine
        parent::createTable($table, $cols);
        
        // create auto-increment triggers
        foreach ($cols as $col_name => $info) {
        	$name = strtoupper($col_name);
            if (! empty($info['autoinc'])) {
                
                // create a sequence for the auto-increment
                $this->_createSequence("{$name}_SEQ", 1);
                
                // create a trigger for the auto-increment.
                // Do NOT reformat to have line breaks.
                // Oracle throws a fit if you do.
                $stmt = "CREATE OR REPLACE TRIGGER \"IN_{$name}\" "
                      . "BEFORE INSERT ON {$table_name} "
                      . "REFERENCING NEW AS NEW "
                      . "FOR EACH ROW BEGIN "
                      . "SELECT {$name}_SEQ.NEXTVAL INTO :NEW.{$name} FROM DUAL; "
                      . "END;";
                
                $this->query($stmt);
            }    
        }
    }
    
    /**
     * 
     * Get the last auto-incremented insert ID from the database.
     * 
     * @param string $table The table name on which the auto-increment occurred.
     * 
     * @param string $col The name of the auto-increment column.
     * 
     * @return int The last auto-increment ID value inserted to the database.
     * 
     */
    public function lastInsertId($table = null, $col = null)
    {
        $col  = strtoupper($col);
        $stmt = "SELECT {$col}_SEQ.CURRVAL FROM DUAL";
        return $this->fetchValue($stmt);
    }
    
    /**
     * 
     * Extend base adapter function to add stringify to the calls.
     * 
     * After connection, set various connection attributes.
     * 
     * @return void
     * 
     */
    protected function _postConnect()
    {
        parent::_postConnect();
        $alter = "ALTER SESSION SET NLS_DATE_FORMAT = 'YYYY-MM-DD'";
        $this->query($alter);
        $this->_pdo->setAttribute(PDO::ATTR_STRINGIFY_FETCHES, true);
    }
}
