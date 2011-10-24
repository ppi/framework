<?php
/**
 * 
 * Abstract base class for specific RDBMS adapters.
 * 
 * When writing an adapter, you need to override these abstract methods:
 * 
 * {{code: php
 *     abstract protected function _fetchTableList();
 *     abstract protected function _fetchTableCols($table);
 *     abstract protected function _createSequence($name, $start = 1);
 *     abstract protected function _dropSequence($name);
 *     abstract protected function _nextSequence($name);
 *     abstract protected function _dropIndex($table, $name);
 *     abstract protected function _modAutoincPrimary(&$coldef, $autoinc, $primary);
 * }}
 * 
 * If the backend needs identifier deconfliction (e.g., PostgreSQL), you will
 * want to override _modIndexName() and _modSequenceName().  Most times this
 * will not be necessary.
 * 
 * If the backend does not have explicit "LIMIT ... OFFSET" support,
 * you will want to override _modSelect($stmt, $parts) to rewrite the query
 * in order to emulate limit/select behavior.  This is particularly necessary
 * for Microsoft SQL and Oracle.
 * 
 * @category Solar
 * 
 * @package Solar_Sql
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Adapter.php 4612 2010-06-19 13:45:49Z pmjones $
 * 
 */
abstract class Solar_Sql_Adapter extends Solar_Base {
    
    /**
     * 
     * Default configuration values.
     * 
     * @config string host Host specification (typically 'localhost').
     * 
     * @config string port Port number for the host name.
     * 
     * @config string sock The Unix socket for the connection. Should not be used with
     *   host and port.
     * 
     * @config string user Connect to the database as this username.
     * 
     * @config string pass Password associated with the username.
     * 
     * @config string name Database name (or file path, or TNS name).
     * 
     * @config bool profiling Turn on query profiling?
     * 
     * @config dependency cache The cache to use, if any, for the lists of
     * table names, table columns, etc.
     * 
     * @var array
     * 
     */
    protected $_Solar_Sql_Adapter = array(
        'host'      => null,
        'port'      => null,
        'sock'      => null,
        'user'      => null,
        'pass'      => null,
        'name'      => null,
        'profiling' => false,
        'cache'     => array('adapter' => 'Solar_Cache_Adapter_Var'),
    );
    
    /**
     * 
     * A cache object for keeping query results.
     * 
     * @var Solar_Cache_Adapter
     * 
     */
    protected $_cache;
    
    /**
     * 
     * Prefix all cache keys with this string.
     * 
     * @var string
     * 
     */
    protected $_cache_key_prefix;
    
    /**
     * 
     * Map of Solar generic types to RDBMS native types used when creating
     * portable tables.
     * 
     * See the individual adapters for specific mappings.
     * 
     * The available generic column types are ...
     * 
     * `char`
     * : A fixed-length string of 1-255 characters.
     * 
     * `varchar`
     * : A variable-length string of 1-255 characters.
     * 
     * `bool`
     * : A true/false boolean, generally stored as an integer 1 or 0.  May
     *   also be stored as null, allowing for ternary logic.
     * 
     * `smallint`
     * : A 2-byte integer in the range of -32767 ... +32768.
     * 
     * `int`
     * : A 4-byte integer in the range of -2,147,483,648 ... +2,147,483,647.
     * 
     * `bigint`
     * : An 8-byte integer, value range roughly (-9,223,372,036,854,780,000
     *   ... +9,223,372,036,854,779,999).
     * 
     * `numeric`
     * : A fixed-point decimal number of a specific size (total number of
     *   digits) and scope (the number of those digits to the right of the
     *   decimal point).
     * 
     * `float`
     * : A double-precision floating-point decimal number.
     * 
     * `clob`
     * : A character large object with a size of up to 2,147,483,647 bytes
     *   (about 2 GB).
     * 
     * `date`
     * : An ISO 8601 date; for example, '1979-11-07'.
     * 
     * `time`
     * : An ISO 8601 time; for example, '12:34:56'.
     * 
     * `timestamp`
     * : An ISO 8601 timestamp without a timezone offset; for example,
     *   '1979-11-07 12:34:56'.
     * 
     * @var array
     * 
     */
    protected $_solar_native = array(
        'bool'      => null,
        'char'      => null, 
        'varchar'   => null, 
        'smallint'  => null,
        'int'       => null,
        'bigint'    => null,
        'numeric'   => null,
        'float'     => null,
        'clob'      => null,
        'date'      => null,
        'time'      => null,
        'timestamp' => null,
    );
    
    /**
     * 
     * Map of native RDBMS types to Solar generic types used when reading 
     * table column information.
     * 
     * See the individual adapters for specific mappings.
     * 
     * @var array
     * 
     * @see fetchTableCols()
     * 
     */
    protected $_native_solar = array();
    
    /**
     * 
     * A PDO object for accessing the RDBMS.
     * 
     * @var object
     * 
     */
    protected $_pdo = null;
    
    /**
     * 
     * The PDO adapter DSN type.
     * 
     * This might not be the same as the Solar adapter type.
     * 
     * @var string
     * 
     */
    protected $_pdo_type = null;
    
    /**
     * 
     * Max identifier lengths for table, column, and index names used when
     * creating portable tables.
     * 
     * We use 30 characters to comply with Oracle maximums.
     * 
     * @var array
     * 
     */
    protected $_maxlen = 30;
    
    /**
     * 
     * A quick-and-dirty query profile array.
     * 
     * Each element is an array, where the first value is the query execution
     * time in microseconds, and the second value is the query string.
     * 
     * Only populated when the `profiling` config key is true.
     * 
     * @var array
     * 
     */
    protected $_profile = array();
    
    /**
     * 
     * Whether or not profiling is turned on.
     * 
     * @var bool
     * 
     */
    protected $_profiling = false;
    
    /**
     * 
     * A PDO-style DSN, for example, "mysql:host=127.0.0.1;dbname=test".
     * 
     * @var string
     * 
     */
    protected $_dsn;
    
    /**
     * 
     * The quote character before an identifier name (table, index, etc).
     * 
     * @var string
     * 
     */
    protected $_ident_quote_prefix = null;
    
    /**
     * 
     * The quote character after an identifier name (table, index, etc).
     * 
     * @var string
     * 
     */
    protected $_ident_quote_suffix = null;
    
    /**
     * 
     * Post-construction tasks to complete object construction.
     * 
     * @return void
     * 
     */
    protected function _postConstruct()
    {
        parent::_postConstruct();
        
        // turn on profiling?
        $this->setProfiling($this->_config['profiling']);
        
        // set a cache object
        $this->_cache = Solar::dependency(
            'Solar_Cache',
            $this->_config['cache']
        );
        
        // follow-on setup
        $this->_setup();
    }
    
    /**
     * 
     * Follow-on setup from the constructor; useful for extended classes.
     * 
     * @return void
     * 
     */
    protected function _setup()
    {
        // set the DSN from the config info
        $this->_setDsn();
        
        // set the cache-key prefix
        $this->setCacheKeyPrefix();
    }
    
    /**
     * 
     * Turns profiling on and off.
     * 
     * @param bool $flag True to turn profiling on, false to turn it off.
     * 
     * @return void
     * 
     */
    public function setProfiling($flag)
    {
        $this->_profiling = (bool) $flag;
    }
    
    /**
     * 
     * Returns the cache object.
     * 
     * @return Solar_Cache
     * 
     * @see $_cache
     * 
     */
    public function getCache()
    {
        return $this->_cache;
    }
    
    /**
     * 
     * Injects a cache dependency for `$_cache`.
     * 
     * @param mixed $spec A [[Solar::dependency()]] specification.
     * 
     * @return void
     * 
     * @see $_cache
     * 
     */
    public function setCache($spec)
    {
        $this->_cache = Solar::dependency('Solar_Cache', $spec);
    }
    
    /**
     * 
     * Sets the connection-specific cache key prefix.
     * 
     * @param string $prefix The cache-key prefix.  When null, defaults to
     * the class name, a slash, and the md5() of the DSN.
     * 
     * @return string
     * 
     */
    public function setCacheKeyPrefix($prefix = null)
    {
        if ($prefix === null) {
            $prefix = get_class($this) . '/' . md5($this->_dsn);
        }
        
        $this->_cache_key_prefix = $prefix;
    }
    
    /**
     * 
     * Gets the connection-specific cache key prefix.
     * 
     * @return string
     * 
     */
    public function getCacheKeyPrefix()
    {
        return $this->_cache_key_prefix;
    }
    
    /**
     * 
     * Get the query profile array.
     * 
     * @return array An array of queries executed by the adapter.
     * 
     */
    public function getProfile()
    {
        return $this->_profile;
    }
    
    /**
     * 
     * Get the PDO connection object (connects to the database if needed).
     * 
     * @return PDO
     * 
     */
    public function getPdo()
    {
        $this->connect();
        return $this->_pdo;
    }
    
    // -----------------------------------------------------------------
    // 
    // Connection and basic queries
    // 
    // -----------------------------------------------------------------
    
    /**
     * 
     * Sets the DSN value for the connection from the config info.
     * 
     * @return void
     * 
     */
    protected function _setDsn()
    {
        $this->_dsn = $this->_buildDsn($this->_config);
    }
    
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
        
        if (! empty($info['host'])) {
            $dsn[] = 'host=' . $info['host'];
        }
        
        if (! empty($info['port'])) {
            $dsn[] = 'port=' . $info['port'];
        }
        
        if (! empty($info['name'])) {
            $dsn[] = 'dbname=' . $info['name'];
        }
        
        return $this->_pdo_type . ':' . implode(';', $dsn);
    }
    
    /**
     * 
     * Creates a PDO object and connects to the database.
     * 
     * Also sets the query-cache key prefix.
     * 
     * @return void
     * 
     */
    public function connect()
    {
        // if we already have a PDO object, no need to re-connect.
        if ($this->_pdo) {
            return;
        }
        
        // start profile time
        $time = microtime(true);
        
        // attempt the connection
        $this->_pdo = new PDO(
            $this->_dsn,
            $this->_config['user'],
            $this->_config['pass']
        );
        
        // retain connection info
        $this->_pdo->solar_conn = array(
            'dsn'  => $this->_dsn,
            'user' => $this->_config['user'],
            'pass' => $this->_config['pass'],
            'type' => 'single',
            'key'  => null,
        );
        
        // post-connection tasks
        $this->_postConnect();
        
        // retain the profile data?
        $this->_addProfile($time, '__CONNECT');
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
        // always emulate prepared statements; this is faster, and works
        // better with CREATE, DROP, ALTER statements.  requires PHP 5.1.3
        // or later. note that we do this *first* (before using exceptions)
        // because not all adapters support it.
        $this->_pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
        $this->_pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
        
        // always use exceptions
        $this->_pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
        // force names to lower case
        $this->_pdo->setAttribute(PDO::ATTR_CASE, PDO::CASE_LOWER);
    }
    
    /**
     * 
     * Closes the database connection.
     * 
     * This isn't generally necessary as PHP will automatically close the
     * connection in the end of the script execution, but it can be useful
     * to free resources when a script needs to connect tomultiple databases
     * in sequence.
     * 
     * @return void
     * 
     */
    public function disconnect()
    {
        $this->_pdo = null;
    }
    
    /**
     * 
     * Gets a full cache key.
     * 
     * @param string $key The partial cache key.
     * 
     * @return string The full cache key.
     * 
     */
    protected function _getCacheKey($key)
    {
        return $this->_cache_key_prefix . "/$key";
    }
    
    /**
     * 
     * Prepares and executes an SQL statement, optionally binding values
     * to named parameters in the statement.
     * 
     * This is the most-direct way to interact with the database; you
     * pass an SQL statement to the method, then the adapter uses
     * [[php::PDO | ]] to execute the statement and return a result.
     * 
     * {{code: php
     *     $sql = Solar::factory('Solar_Sql');
     * 
     *     // $result is a PDOStatement
     *     $result = $sql->query('SELECT * FROM table');
     * }}
     * 
     * To help prevent SQL injection attacks, you should **always** quote
     * the values used in a direct query. Use [[Solar_Sql_Adapter::quote() | quote()]],
     * [[Solar_Sql_Adapter::quoteInto() | quoteInto()]], or 
     * [[Solar_Sql_Adapter::quoteMulti() | quoteMulti()]] to accomplish this.
     * Even easier, use the automated value binding provided by the query() 
     * method:
     * 
     * {{code: php
     *     // BAD AND SCARY:
     *     $result = $sql->query('SELECT * FROM table WHERE foo = $bar');
     *     
     *     // Much much better:
     *     $result = $sql->query(
     *         'SELECT * FROM table WHERE foo = :bar',
     *         array('bar' => $bar)
     *     );
     * }}
     * 
     * Note that adapters provide convenience methods to automatically quote
     * values on common operations:
     * 
     * - [[Solar_Sql_Adapter::insert()]]
     * - [[Solar_Sql_Adapter::update()]]
     * - [[Solar_Sql_Adapter::delete()]]
     * 
     * Additionally, the [[Solar_Sql_Select]] class is dedicated to
     * safely creating portable SELECT statements, so you may wish to use that
     * instead of writing literal SELECTs.
     * 
     * 
     * Automated Binding of Values in PHP 5.2.1 and Later
     * --------------------------------------------------
     * 
     * With PDO in PHP 5.2.1 and later, we can no longer just throw an array
     * of data at the statement for binding. We now need to bind values
     * specifically to their respective placeholders.
     * 
     * In addition, we can't bind one value to multiple identical named
     * placeholders; we need to bind that same value multiple times. So if
     * `:foo` is used three times, PDO uses `:foo` the first time, `:foo2` the
     * second time, and `:foo3` the third time.
     * 
     * This query() method examins the statement for all `:name` placeholders
     * and attempts to bind data from the `$data` array.  The regular-expression
     * it uses is a little braindead; it cannot tell if the :name placeholder
     * is literal text or really a place holder.
     * 
     * As such, you should *either* use the `$data` array for named-placeholder
     * value binding at query() time, *or* bind-as-you-go when building the 
     * statement, not both.  If you do, you are on your own to make sure
     * that nothing looking like a `:name` placeholder exists in the literal text.
     * 
     * Question-mark placeholders are not supported for automatic value
     * binding at query() time.
     * 
     * @param string $stmt The text of the SQL statement, optionally with
     * named placeholders.
     * 
     * @param array $data An associative array of data to bind to the named
     * placeholders.
     * 
     * @return PDOStatement
     * 
     */
    public function query($stmt, $data = array())
    {
        $this->connect();
        
        // begin the profile time
        $time = microtime(true);
        
        // prepre the statement and bind data to it
        $prep = $this->_prepare($stmt);
        $this->_bind($prep, $data);
        
        // now try to execute
        try {
            $prep->execute();
        } catch (PDOException $e) {
            throw $this->_exception('ERR_QUERY_FAILED', array(
                'pdo_code'  => $e->getCode(),
                'pdo_text'  => $e->getMessage(),
                'host'      => $this->_config['host'],
                'port'      => $this->_config['port'],
                'user'      => $this->_config['user'],
                'name'      => $this->_config['name'],
                'stmt'      => $stmt,
                'data'      => $data,
                'pdo_trace' => $e->getTraceAsString(),
            ));
        }
        
        // retain the profile data?
        $this->_addProfile($time, $prep, $data);
        
        // done!
        return $prep;
    }
    
    /**
     * 
     * Prepares an SQL query as a PDOStatement object.
     * 
     * @param string $stmt The text of the SQL statement, optionally with
     * named placeholders.
     * 
     * @return PDOStatement
     * 
     */
    protected function _prepare($stmt)
    {
        // prepare the statment
        try {
            $prep = $this->_pdo->prepare($stmt);
            $prep->solar_conn = $this->_pdo->solar_conn;
        } catch (PDOException $e) {
            throw $this->_exception('ERR_PREPARE_FAILED', array(
                'pdo_code'  => $e->getCode(),
                'pdo_text'  => $e->getMessage(),
                'host'      => $this->_config['host'],
                'port'      => $this->_config['port'],
                'sock'      => $this->_config['sock'],
                'user'      => $this->_config['user'],
                'name'      => $this->_config['name'],
                'stmt'      => $stmt,
                'pdo_trace' => $e->getTraceAsString(),
            ));
        }
        
        return $prep;
    }
    
    /**
     * 
     * Binds an array of scalars as values into a prepared PDOStatment.
     * 
     * Array element values that are themselves arrays will not be bound
     * correctly, because PDO expects scalar values only.
     * 
     * @param PDOStatement $prep The prepared PDOStatement.
     * 
     * @param array $data The scalar values to bind into the PDOStatement.
     * 
     * @return void
     * 
     */
    protected function _bind($prep, $data)
    {
        // was data passed for binding?
        if (! $data) {
            return;
        }
            
        // find all :placeholder matches.  note that this is a little
        // brain-dead; it will find placeholders in literal text, which
        // will cause errors later.  so in general, you should *either*
        // bind at query time *or* bind as you go, not both.
        preg_match_all(
            "/\W:([a-zA-Z_][a-zA-Z0-9_]*)/m",
            $prep->queryString . "\n",
            $matches
        );
        
        // bind values to placeholders, repeating as needed
        $repeat = array();
        foreach ($matches[1] as $key) {
            
            // only attempt to bind if the data key exists.
            // this allows for nulls and empty strings.
            if (! array_key_exists($key, $data)) {
                // skip it
                continue;
            }
        
            // what does PDO expect as the placeholder name?
            if (empty($repeat[$key])) {
                // first time is ":foo"
                $repeat[$key] = 1;
                $name = $key;
            } else {
                // repeated times of ":foo" are treated by PDO as
                // ":foo2", ":foo3", etc.
                $repeat[$key] ++;
                $name = $key . $repeat[$key];
            }
            
            // bind the value to the placeholder name
            $prep->bindValue($name, $data[$key]);
        }
    }
    
    /**
     * 
     * Adds an element to the profile array.
     * 
     * @param int $time The microtime when the profile element started.
     * 
     * @param string|PDOStatement $spec The SQL statement being profiled.
     * 
     * @param array $data Any data bound into the statement.
     * 
     * @return void
     * 
     */
    protected function _addProfile($time, $spec, $data = null)
    {
        if (! $this->_profiling) {
            return;
        }
        
        if ($spec instanceof PDOStatement) {
            $conn = $spec->solar_conn;
            $stmt = $spec->queryString;
        } else {
            $conn = null;
            $stmt = $spec;
        }
        
        $timespan = microtime(true) - $time;
        $e = new Exception();
        $this->_profile[] = array(
            'time'      => $timespan,
            'stmt'      => $stmt,
            'data'      => $data,
            'conn'      => $conn,
            'trace'     => $e->getTraceAsString(),
        );
    }
    
    // -----------------------------------------------------------------
    // 
    // Transactions
    // 
    // -----------------------------------------------------------------
    
    /**
     * 
     * Leave autocommit mode and begin a transaction.
     * 
     * @return void
     * 
     */
    public function begin()
    {
        $this->connect();
        $time = microtime(true);
        $result = $this->_pdo->beginTransaction();
        $this->_addProfile($time, '__BEGIN');
        return $result;
    }
    
    /**
     * 
     * Commit a transaction and return to autocommit mode.
     * 
     * @return void
     * 
     */
    public function commit()
    {
        $this->connect();
        $time = microtime(true);
        $result = $this->_pdo->commit();
        $this->_addProfile($time, '__COMMIT');
        return $result;
    }
    
    /**
     * 
     * Roll back a transaction and return to autocommit mode.
     * 
     * @return void
     * 
     */
    public function rollback()
    {
        $this->connect();
        $time = microtime(true);
        $result = $this->_pdo->rollBack();
        $this->_addProfile($time, '__ROLLBACK');
        return $result;
    }
    
    // -----------------------------------------------------------------
    // 
    // Manipulation
    // 
    // -----------------------------------------------------------------
    
    /**
     * 
     * Inserts a row of data into a table.
     * 
     * Automatically applies [[Solar_Sql_Adapter::quote() | ]] to the data 
     * values for you.
     * 
     * For example:
     * 
     * {{code: php
     *     $sql = Solar::factory('Solar_Sql');
     * 
     *     $table = 'invaders';
     *     $data = array(
     *         'foo' => 'bar',
     *         'baz' => 'dib',
     *         'zim' => 'gir'
     *     );
     * 
     *     $rows_affected = $sql->insert($table, $data);
     *     // calls 'INSERT INTO invaders (foo, baz, zim) VALUES ("bar", "dib", "gir")'
     * }}
     * 
     * @param string $table The table to insert data into.
     * 
     * @param array $data An associative array where the key is the column
     * name and the value is the value to insert for that column.
     * 
     * @return int The number of rows affected, typically 1.
     * 
     */
    public function insert($table, $data)
    {
        // the base statement
        $table = $this->quoteName($table);
        $stmt = "INSERT INTO $table ";
        
        // col names come from the array keys
        $keys = array_keys($data);
        
        // quote the col names
        $cols = array();
        foreach ($keys as $key) {
            $cols[] = $this->quoteName($key);
        }
        
        // add quoted col names
        $stmt .= '(' . implode(', ', $cols) . ') ';
        
        // add value placeholders (use unquoted key names)
        $stmt .= 'VALUES (:' . implode(', :', $keys) . ')';
        
        // execute the statement
        $result = $this->query($stmt, $data);
        return $result->rowCount();
    }
    
    /**
     * 
     * Updates a table with specified data based on a WHERE clause.
     * 
     * Automatically applies [[Solar_Sql_Adapter::quote() | ]] to the data 
     * values for you.
     * 
     * @param string $table The table to udpate.
     * 
     * @param array $data An associative array where the key is the column
     * name and the value is the value to use for that column.
     * 
     * @param string|array $where The SQL WHERE clause to limit which
     * rows are updated.
     * 
     * @return int The number of rows affected.
     * 
     */
    public function update($table, $data, $where)
    {
        // the base statement
        $table = $this->quoteName($table);
        $stmt = "UPDATE $table SET ";
        
        // add "col = :col" pairs to the statement
        $tmp = array();
        foreach ($data as $col => $val) {
            $tmp[] = $this->quoteName($col) . " = :$col";
        }
        $stmt .= implode(', ', $tmp);
        
        // add the where clause
        if ($where) {
            $where = $this->quoteMulti($where, ' AND ');
            $where = $this->quoteNamesIn($where);
            $stmt .= " WHERE $where";
        }
        
        // execute the statement
        $result = $this->query($stmt, $data);
        return $result->rowCount();
    }
    
    /**
     * 
     * Deletes rows from the table based on a WHERE clause.
     * 
     * For example ...
     * 
     * {{code: php
     *     $sql = Solar::factory('Solar_Sql');
     * 
     *     $table = 'events';
     *     $where = $sql->quoteInto('status = ?', 'cancelled');
     *     $rows_affected = $sql->delete($table, $where);
     * 
     *     // calls 'DELETE FROM events WHERE status = "cancelled"'
     * }}
     * 
     * For the $where parameter, you can also pass multiple WHERE conditions as
     * an array to be "AND"ed together.
     * 
     * {{code: php
     *     $sql = Solar::factory('Solar_Sql');
     * 
     *     $table = 'events';
     *     $where = array(
     *         "date >= ?"  => '2006-01-01',
     *         "date <= ?"  => '2006-01-31',
     *         "status = ?" => 'cancelled',
     *     );
     * 
     *     $rows_affected = $sql->delete($table, $where);
     * 
     *     // calls:
     *     // DELETE FROM events WHERE date >= "2006-01-01"
     *     // AND date <= "2006-01-31" AND status = "cancelled"
     * }}
     * 
     * @param string $table The table to delete from.
     * 
     * @param string|array $where The SQL WHERE clause to limit which
     * rows are deleted.
     * 
     * @return int The number of rows affected.
     * 
     */
    public function delete($table, $where)
    {
        if ($where) {
            $where = $this->quoteMulti($where, ' AND ');
            $where = $this->quoteNamesIn($where);
        }
        
        $table = $this->quoteName($table);
        $result = $this->query("DELETE FROM $table WHERE $where");
        return $result->rowCount();
    }
    
    // -----------------------------------------------------------------
    // 
    // Retrieval
    // 
    // -----------------------------------------------------------------
    
    /**
     * 
     * Fetches all rows from the database using sequential keys.
     * 
     * @param array|string $spec An array of component parts for a
     * SELECT, or a literal query string.
     * 
     * @param array $data An associative array of data to bind into the
     * SELECT statement.
     * 
     * @return array
     * 
     */
    public function fetchAll($spec, $data = array())
    {
        $result = $this->fetchPdo($spec, $data);
        return $result->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * 
     * Fetches all rows from the database using associative keys (defined by
     * the first column).
     * 
     * N.b.: if multiple rows have the same first column value, the last
     * row with that value will override earlier rows.
     * 
     * @param array|string $spec An array of component parts for a
     * SELECT, or a literal query string.
     * 
     * @param array $data An associative array of data to bind into the
     * SELECT statement.
     * 
     * @return array
     * 
     */
    public function fetchAssoc($spec, $data = array())
    {
        $result = $this->fetchPdo($spec, $data);
        
        $data = array();
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            $key = current($row); // value of the first element
            $data[$key] = $row;
        }
        
        return $data;
    }
    
    /**
     * 
     * Fetches the first column of all rows as a sequential array.
     * 
     * @param array|string $spec An array of component parts for a
     * SELECT, or a literal query string.
     * 
     * @param array $data An associative array of data to bind into the
     * SELECT statement.
     * 
     * @return array
     * 
     */
    public function fetchCol($spec, $data = array())
    {
        $result = $this->fetchPdo($spec, $data);
        return $result->fetchAll(PDO::FETCH_COLUMN, 0);
    }
    
    /**
     * 
     * Fetches the very first value (i.e., first column of the first row).
     * 
     * When $spec is an array, automatically sets LIMIT 1 OFFSET 0 to limit
     * the results to a single row.
     * 
     * @param array|string $spec An array of component parts for a
     * SELECT, or a literal query string.
     * 
     * @param array $data An associative array of data to bind into the
     * SELECT statement.
     * 
     * @return mixed
     * 
     */
    public function fetchValue($spec, $data = array())
    {
        if (is_array($spec)) {
            // automatically limit to the first row only,
            // but leave the offset alone.
            $spec['limit']['count'] = 1;
        }
        $result = $this->fetchPdo($spec, $data);
        return $result->fetchColumn(0);
    }
    
    /**
     * 
     * Fetches an associative array of all rows as key-value pairs (first 
     * column is the key, second column is the value).
     * 
     * @param array|string $spec An array of component parts for a
     * SELECT, or a literal query string.
     * 
     * @param array $data An associative array of data to bind into the
     * SELECT statement.
     * 
     * @return array
     * 
     */
    public function fetchPairs($spec, $data = array())
    {
        $result = $this->fetchPdo($spec, $data);
        
        $data = array();
        while ($row = $result->fetch(PDO::FETCH_NUM)) {
            $data[$row[0]] = $row[1];
        }
        
        return $data;
    }
    
    /**
     * 
     * Fetches a PDOStatement result object.
     * 
     * @param array|string $spec An array of component parts for a
     * SELECT, or a literal query string.
     * 
     * @param array $data An associative array of data to bind into the
     * SELECT statement.
     * 
     * @return PDOStatement
     * 
     */
    public function fetchPdo($spec, $data = array())
    {
        // build the statement from its component parts if needed
        if (is_array($spec)) {
            $stmt = $this->_select($spec);
        } else {
            $stmt = $spec;
        }
        
        // execute and get the PDOStatement result object
        return $this->query($stmt, $data);
    }
    
    /**
     * 
     * Fetches one row from the database.
     * 
     * When $spec is an array, automatically sets LIMIT 1 OFFSET 0 to limit
     * the results to a single row.
     * 
     * @param array|string $spec An array of component parts for a
     * SELECT, or a literal query string.
     * 
     * @param array $data An associative array of data to bind into the
     * SELECT statement.
     * 
     * @return array
     * 
     */
    public function fetchOne($spec, $data = array())
    {
        if (is_array($spec)) {
            // automatically limit to the first row only,
            // but leave the offset alone.
            $spec['limit']['count'] = 1;
        }
        
        $result = $this->fetchPdo($spec, $data);
        return $result->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * 
     * Builds the SQL statement and returns it as a string instead of 
     * executing it.  Useful for debugging.
     * 
     * @param array|string $spec An array of component parts for a
     * SELECT, or a literal query string.
     * 
     * @return string
     * 
     */
    public function fetchSql($spec)
    {
        // build the statement from its component parts if needed
        if (is_array($spec)) {
            return $this->_select($spec);
        } else {
            return $spec;
        }
    }
    
    /**
     * 
     * Returns a SELECT statement built from its component parts.
     * 
     * @param array $parts The component parts of the SELECT.
     * 
     * @return string The SELECT string.
     * 
     */
    protected function _select($parts)
    {
        // buid the statment
        if (empty($parts['compound'])) {
            $stmt = $this->_selectSingle($parts);
        } else {
            $stmt = $this->_selectCompound($parts);
        }
        
        // modify per adapter
        $this->_modSelect($stmt, $parts);
        
        // done!
        return $stmt;
    }
    
    /**
     * 
     * Builds a single SELECT command string from its component parts, 
     * without the LIMIT portions; those are left to the individual adapters.
     * 
     * @param array $parts The parts of the SELECT statement, generally
     * from a Solar_Sql_Select object.
     * 
     * @return string A SELECT command string.
     * 
     */
    protected function _selectSingle($parts)
    {
        $default = array(
            'distinct' => null,
            'cols'     => array(),
            'from'     => array(),
            'join'     => array(),
            'where'    => array(),
            'group'    => array(),
            'having'   => array(),
            'order'    => array(),
        );
        
        $parts = array_merge($default, $parts);
        
        // is this a SELECT or SELECT DISTINCT?
        if ($parts['distinct']) {
            $stmt = "SELECT DISTINCT\n    ";
        } else {
            $stmt = "SELECT\n    ";
        }
        
        // add columns
        $stmt .= implode(",\n    ", $parts['cols']) . "\n";
        
        // from these tables
        $stmt .= $this->_selectSingleFrom($parts['from']);
        
        // joined to these tables
        if ($parts['join']) {
            $list = array();
            foreach ($parts['join'] as $join) {
                $tmp = '';
                // add the type (LEFT, INNER, etc)
                if (! empty($join['type'])) {
                    $tmp .= $join['type'] . ' ';
                }
                // add the table name and condition
                $tmp .= 'JOIN ' . $join['name'];
                $tmp .= ' ON ' . $join['cond'];
                // add to the list
                $list[] = $tmp;
            }
            // add the list of all joins
            $stmt .= implode("\n", $list) . "\n";
        }
        
        // with these where conditions
        if ($parts['where']) {
            $stmt .= "WHERE\n    ";
            $stmt .= implode("\n    ", $parts['where']) . "\n";
        }
        
        // grouped by these columns
        if ($parts['group']) {
            $stmt .= "GROUP BY\n    ";
            $stmt .= implode(",\n    ", $parts['group']) . "\n";
        }
        
        // having these conditions
        if ($parts['having']) {
            $stmt .= "HAVING\n    ";
            $stmt .= implode("\n    ", $parts['having']) . "\n";
        }
        
        // ordered by these columns
        if ($parts['order']) {
            $stmt .= "ORDER BY\n    ";
            $stmt .= implode(",\n    ", $parts['order']) . "\n";
        }
        
        // done!
        return $stmt;
    }
    
    /**
     * 
     * Builds the FROM clause for a SELECT command.
     * 
     * @param array $from The array of FROM clause elements.
     * 
     * @return string The FROM clause.
     * 
     */
    protected function _selectSingleFrom($from)
    {
        return "FROM\n    "
             . implode(",\n    ", $from)
             . "\n";
    }
    
    /**
     * 
     * Builds a compound SELECT command string from its component parts,
     * without the LIMIT portions; those are left to the individual adapters.
     * 
     * @param array $parts The parts of the SELECT statement, generally
     * from a Solar_Sql_Select object.
     * 
     * @return string A SELECT command string.
     * 
     */
    protected function _selectCompound($parts)
    {
        // the select statement to build up
        $stmt = '';
        
        // default parts of each 'compound' element
        $default = array(
            'type' => null, // 'UNION', 'UNION ALL', etc.
            'spec' => null, // array or string for the SELECT statement
        );
        
        // combine the compound elements
        foreach ((array) $parts['compound'] as $compound) {
            
            // make sure we have the default elements
            $compound = array_merge($default, $compound);
            
            // is it an array of select parts?
            if (is_array($compound['spec'])) {
                // yes, build a select string from them
                $select = $this->_select($compound['spec']);
            } else {
                // no, assume it's already a select string
                $select = $compound['spec'];
            }
            
            // do we need to add the compound type?
            // note that the first compound type will be ignored.
            if ($stmt) {
                $stmt .= strtoupper($compound['type']) . "\n";
            }
            
            // now add the select itself
            $stmt .= "(" . $select . ")\n";
        }
        
        // add any overall order
        if (! empty($parts['order'])) {
            $stmt .= "ORDER BY\n    ";
            $stmt .= implode(",\n    ", $parts['order']) . "\n";
        }
        
        // done!
        return $stmt;
    }
    
    /**
     * 
     * Modifies a SELECT statement in place to add a LIMIT clause.
     * 
     * The default code adds a LIMIT for MySQL, PostgreSQL, and Sqlite, but
     * adapters can override as needed.
     * 
     * @param string &$stmt The SELECT statement.
     * 
     * @param array &$parts The orignal SELECT component parts, in case the
     * adapter needs them.
     * 
     * @return void
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
            $stmt .= "LIMIT $count";
            if ($offset > 0) {
                $stmt .= " OFFSET $offset";
            }
        }
    }
    
    
    // -----------------------------------------------------------------
    // 
    // Quoting
    // 
    // -----------------------------------------------------------------
    
    /**
     * 
     * Safely quotes a value for an SQL statement.
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
        if (is_array($val)) {
            // quote array values, not keys, then combine with commas.
            foreach ($val as $k => $v) {
                $val[$k] = $this->quote($v);
            }
            return implode(', ', $val);
        } else {
            // quote all other scalars, including numerics
            $this->connect();
            return $this->_pdo->quote($val);
        }
    }
    
    /**
     * 
     * Quotes a value and places into a piece of text at a placeholder; the
     * placeholder is a question-mark.
     * 
     * {{code: php
     *      $sql = Solar::factory('Solar_Sql');
     *      
     *      // replace one placeholder
     *      $text = "WHERE date >= ?";
     *      $data = "2005-01-01";
     *      $safe = $sql->quoteInto($text, $data);
     *      // => "WHERE date >= '2005-01-02'"
     *      
     *      // replace multiple placeholders
     *      $text = "WHERE date BETWEEN ? AND ?";
     *      $data = array("2005-01-01", "2005-01-31");
     *      $safe = $sql->quoteInto($text, $data);
     *      // => "WHERE date BETWEEN '2005-01-01' AND '2005-01-31'"
     * 
     *      // single placeholder with array value
     *      $text = "WHERE foo IN (?)";
     *      $data = array('a', 'b', 'c');
     *      $safe = $sql->quoteInto($text, $data);
     *      // => "WHERE foo IN ('a', 'b', 'c')"
     *      
     *      // multiple placeholders and array values
     *      $text = "WHERE date >= ? AND foo IN (?)";
     *      $data = array('2005-01-01, array('a', 'b', 'c'));
     *      $safe = $sql->quoteInto($text, $data);
     *      // => "WHERE date >= '2005-01-01' AND foo IN ('a', 'b', 'c')"
     * }}
     * 
     * @param string $text The text with placeholder(s).
     * 
     * @param mixed $data The data value(s) to quote.
     * 
     * @return mixed An SQL-safe quoted value (or string of separated values)
     * placed into the orignal text.
     * 
     * @see quote()
     * 
     */
    public function quoteInto($text, $data)
    {
        // how many question marks are there?
        $count = substr_count($text, '?');
        if (! $count) {
            // no replacements needed
            return $text;
        }
        
        // only one replacement?
        if ($count == 1) {
            $data = $this->quote($data);
            $text = str_replace('?', $data, $text);
            return $text;
        }
        
        // more than one replacement; force values to be an array, then make 
        // sure we have enough values to replace all the placeholders.
        settype($data, 'array');
        if (count($data) < $count) {
            // more placeholders than values
            throw $this->_exception('ERR_NOT_ENOUGH_VALUES', array(
                'text'  => $text,
                'data'  => $data,
            ));
        }
        
        // replace each placeholder with a quoted value
        $offset = 0;
        foreach ($data as $val) {
            // find the next placeholder
            $pos = strpos($text, '?', $offset);
            if ($pos === false) {
                // no more placeholders, exit the data loop
                break;
            }
            
            // replace this question mark with a quoted value
            $val  = $this->quote($val);
            $text = substr_replace($text, $val, $pos, 1);
            
            // update the offset to move us past the quoted value
            $offset = $pos + strlen($val);
        }
        
        return $text;
    }
    
    /**
     * 
     * Quote multiple text-and-value pieces.
     * 
     * The placeholder is a question-mark; all placeholders will be replaced
     * with the quoted value.   For example ...
     * 
     * {{code: php
     *     $sql = Solar::factory('Solar_Sql');
     *     
     *     $list = array(
     *          "WHERE date > ?"   => '2005-01-01',
     *          "  AND date < ?"   => '2005-02-01',
     *          "  AND type IN(?)" => array('a', 'b', 'c'),
     *     );
     *     $safe = $sql->quoteMulti($list);
     *     
     *     // $safe = "WHERE date > '2005-01-02'
     *     //          AND date < 2005-02-01
     *     //          AND type IN('a','b','c')"
     * }}
     * 
     * @param array $list A series of key-value pairs where the key is
     * the placeholder text and the value is the value to be quoted into
     * it.  If the key is an integer, it is assumed that the value is
     * piece of literal text to be used and not quoted.
     * 
     * @param string $sep Return the list pieces separated with this string
     * (for example ' AND '), default null.
     * 
     * @return string An SQL-safe string composed of the list keys and
     * quoted values.
     * 
     */
    public function quoteMulti($list, $sep = null)
    {
        $text = array();
        foreach ((array) $list as $key => $val) {
            if (is_int($key)) {
                // integer $key means a literal phrase and no value to
                // be bound into it
                $text[] = $val;
            } else {
                // string $key means a phrase with a placeholder, and
                // $val should be bound into it.
                $text[] = $this->quoteInto($key, $val); 
            }
        }
        
        // return the condition list
        $result = implode($sep, $text);
        return $result;
    }
    
    /**
     * 
     * Quotes a single identifier name (table, table alias, table column, 
     * index, sequence).  Ignores empty values.
     * 
     * If the name contains ' AS ', this method will separately quote the
     * parts before and after the ' AS '.
     * 
     * If the name contains a space, this method will separately quote the
     * parts before and after the space.
     * 
     * If the name contains a dot, this method will separately quote the
     * parts before and after the dot.
     * 
     * @param string|array $spec The identifier name to quote.  If an array,
     * quotes each element in the array as an identifier name.
     * 
     * @return string|array The quoted identifier name (or array of names).
     * 
     * @see _quoteName()
     * 
     */
    public function quoteName($spec)
    {
        if (is_array($spec)) {
            foreach ($spec as $key => $val) {
                $spec[$key] = $this->quoteName($val);
            }
            return $spec;
        }
        
        // no extraneous spaces
        $spec = trim($spec);
        
        // `original` AS `alias` ... note the 'rr' in strripos
        $pos = strripos($spec, ' AS ');
        if ($pos) {
            // recurse to allow for "table.col"
            $orig  = $this->quoteName(substr($spec, 0, $pos));
            // use as-is
            $alias = $this->_quoteName(substr($spec, $pos + 4));
            return "$orig AS $alias";
        }
        
        // `original` `alias`
        $pos = strrpos($spec, ' ');
        if ($pos) {
            // recurse to allow for "table.col"
            $orig = $this->quoteName(substr($spec, 0, $pos));
            // use as-is
            $alias = $this->_quoteName(substr($spec, $pos + 1));
            return "$orig $alias";
        }
        
        // `table`.`column`
        $pos = strrpos($spec, '.');
        if ($pos) {
            // use both as-is
            $table = $this->_quoteName(substr($spec, 0, $pos));
            $col   = $this->_quoteName(substr($spec, $pos + 1));
            return "$table.$col";
        }
        
        // `name`
        return $this->_quoteName($spec);
    }
    
    /**
     * 
     * Quotes an identifier name (table, index, etc); ignores empty values and
     * values of '*'.
     * 
     * @param string $name The identifier name to quote.
     * 
     * @return string The quoted identifier name.
     * 
     * @see quoteName()
     * 
     */
    protected function _quoteName($name)
    {
        $name = trim($name);
        if ($name == '*') {
            return $name;
        } else {
            return $this->_ident_quote_prefix
                 . $name
                 . $this->_ident_quote_suffix;
        }
    }
    
    /**
     * 
     * Quotes all fully-qualified identifier names ("table.col") in a string,
     * typically an SQL snippet for a SELECT clause.
     * 
     * Does not quote identifier names that are string literals (i.e., inside
     * single or double quotes).
     * 
     * Looks for a trailing ' AS alias' and quotes the alias as well.
     * 
     * @param string|array $spec The string in which to quote fully-qualified
     * identifier names to quote.  If an array, quotes names in each element
     * in the array.
     * 
     * @return string|array The string (or array) with names quoted in it.
     * 
     * @see _quoteNamesIn()
     * 
     */
    public function quoteNamesIn($spec)
    {
        if (is_array($spec)) {
            foreach ($spec as $key => $val) {
                $spec[$key] = $this->quoteNamesIn($val);
            }
            return $spec;
        }
        
        // single and double quotes
        $apos = "'";
        $quot = '"';
        
        // look for ', ", \', or \" in the string.
        // match closing quotes against the same number of opening quotes.
        $list = preg_split(
            "/(($apos+|$quot+|\\$apos+|\\$quot+).*?\\2)/",
            $spec,
            -1,
            PREG_SPLIT_DELIM_CAPTURE
        );
        
        // concat the pieces back together, quoting names as we go.
        $spec = null;
        $last = count($list) - 1;
        foreach ($list as $key => $val) {
            
            // skip elements 2, 5, 8, 11, etc. as artifacts of the back-
            // referenced split; these are the trailing/ending quote
            // portions, and already included in the previous element.
            // this is the same as every third element from zero.
            if (($key+1) % 3 == 0) {
                continue;
            }
            
            // is there an apos or quot anywhere in the part?
            $is_string = strpos($val, $apos) !== false ||
                         strpos($val, $quot) !== false;
            
            if ($is_string) {
                // string literal
                $spec .= $val;
            } else {
                // sql language.
                // look for an AS alias if this is the last element.
                if ($key == $last) {
                    // note the 'rr' in strripos
                    $pos = strripos($val, ' AS ');
                    if ($pos) {
                        // quote the alias name directly
                        $alias = $this->_quoteName(substr($val, $pos + 4));
                        $val = substr($val, 0, $pos) . " AS $alias";
                    }
                }
                
                // now quote names in the language.
                $spec .= $this->_quoteNamesIn($val);
            }
        }
        
        // done!
        return $spec;
    }
    
    /**
     * 
     * Quotes all fully-qualified identifier names ("table.col") in a string.
     * 
     * @param string|array $text The string in which to quote fully-qualified
     * identifier names to quote.  If an array, quotes names in  each 
     * element in the array.
     * 
     * @return string|array The string (or array) with names quoted in it.
     * 
     * @see quoteNamesIn()
     * 
     */
    protected function _quoteNamesIn($text)
    {
        $word = "[a-z_][a-z0-9_]+";
        
        $find = "/(\\b)($word)\\.($word)(\\b)/i";
        
        $repl = '$1'
              . $this->_ident_quote_prefix
              . '$2'
              . $this->_ident_quote_suffix
              . '.'
              . $this->_ident_quote_prefix
              . '$3'
              . $this->_ident_quote_suffix
              . '$4'
              ;
              
        $text = preg_replace($find, $repl, $text);
        
        return $text;
    }
    
    
    // -----------------------------------------------------------------
    // 
    // Auto-increment and sequence reading.
    // 
    // -----------------------------------------------------------------
    
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
        $this->connect();
        return $this->_pdo->lastInsertId();
    }
    
    /**
     * 
     * Gets the next number in a sequence; creates the sequence if it does not exist.
     * 
     * @param string $name The sequence name; this will be 
     * automatically suffixed with '__s' for portability reasons.
     * 
     * @return int The next number in the sequence.
     * 
     */
    public function nextSequence($name)
    {
        $name = $this->_modSequenceName($name);
        $result = $this->_nextSequence($name);
        return $result;
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
    abstract protected function _nextSequence($name);
    
    
    // -----------------------------------------------------------------
    // 
    // Table and column information reading.
    // 
    // -----------------------------------------------------------------
    
    /**
     * 
     * Returns a list of database tables from the cache; if the cache entry
     * is not available, queries the database for the list of tables.
     * 
     * @param string $schema Fetch tbe list of tables in this database
     * schema; when empty, uses the current or default schema.
     * 
     * @return array A sequential array of table names in the database.
     * 
     */
    public function fetchTableList($schema = null)
    {
        if ($schema) {
            $key = $this->_getCacheKey("table_list/$schema");
        } else {
            $key = $this->_getCacheKey("table_list");
        }
        
        $result = $this->_cache->fetch($key);
        if (! $result) {
            $result = $this->_fetchTableList($schema);
            $this->_cache->add($key, $result);
        }
        return $result;
    }
    
    /**
     * 
     * Returns a list of database tables.
     * 
     * @param string $schema Fetch tbe list of tables in this database
     * schema; when empty, uses the current or default schema.
     * 
     * @return array A sequential array of table names in the database.
     * 
     */
    abstract protected function _fetchTableList($schema);
    
    /**
     * 
     * Returns an array describing table columns from the cache; if the cache
     * entry is not available, queries the database for the column
     * descriptions.
     * 
     * @param string $spec The table or schema.table to fetch columns for.
     * 
     * @return array An array of table columns.
     * 
     */
    public function fetchTableCols($spec)
    {
        $key = $this->_getCacheKey("table/$spec/cols");
        $result = $this->_cache->fetch($key);
        if (! $result) {
            list($schema, $table) = $this->_splitSchemaIdent($spec);
            $result = $this->_fetchTableCols($table, $schema);
            $this->_cache->add($key, $result);
        }
        return $result;
    }
    
    /**
     * 
     * Returns an array describing the columns in a table.
     * 
     * @param string $table The table name to fetch columns for.
     * 
     * @param string $schema The schema in which the table resides.
     * 
     * @return array An array of table columns.
     * 
     */
    abstract protected function _fetchTableCols($table, $schema);
    
    /**
     * 
     * Given a column specification, parse into datatype, size, and 
     * decimal scope.
     * 
     * @param string $spec The column specification; for example,
     * "VARCHAR(255)" or "NUMERIC(10,2)".
     * 
     * @return array A sequential array of the column type, size, and scope.
     * 
     */
    protected function _getTypeSizeScope($spec)
    {
        $spec  = strtolower($spec);
        $type  = null;
        $size  = null;
        $scope = null;
        
        // find the parens, if any
        $pos = strpos($spec, '(');
        if ($pos === false) {
            // no parens, so no size or scope
            $type = $spec;
        } else {
            // find the type first.
            $type = substr($spec, 0, $pos);
            
            // there were parens, so there's at least a size.
            // remove parens to get the size.
            $size = trim(substr($spec, $pos), '()');
            
            // a comma in the size indicates a scope.
            $pos = strpos($size, ',');
            if ($pos !== false) {
                $scope = substr($size, $pos + 1);
                $size  = substr($size, 0, $pos);
            }
        }
        
        foreach ($this->_native_solar as $native => $solar) {
            // $type is already lowered
            if ($type == strtolower($native)) {
                $type = strtolower($solar);
                break;
            }
        }
        
        return array($type, $size, $scope);
    }
    
    /**
     * 
     * Returns an array describing table indexes from the cache; if the cache
     * entry is not available, queries the database for the index information.
     * 
     * @param string $spec The table or schema.table name to fetch indexes
     * for.
     * 
     * @return array An array of table indexes.
     * 
     */
    public function fetchIndexInfo($spec)
    {
        $key = $this->_getCacheKey("table/$spec/index");
        $result = $this->_cache->fetch($key);
        if (! $result) {
            list($schema, $table) = $this->_splitSchemaIdent($spec);
            $result = $this->_fetchIndexInfo($table, $schema);
            $this->_cache->add($key, $result);
        }
        return $result;
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
    abstract protected function _fetchIndexInfo($table, $schema);
    
    // -----------------------------------------------------------------
    // 
    // Table, column, index, and sequence management.
    // 
    // -----------------------------------------------------------------
    
    /**
     * 
     * Creates a portable table.
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
     */
    public function createTable($table, $cols)
    {
        $this->_cache->deleteAll();
        $stmt = $this->_sqlCreateTable($table, $cols);
        $this->query($stmt);
    }
    
    /**
     * 
     * Returns a CREATE TABLE command string for the adapter.
     * 
     * We use this so that certain adapters can append table types
     * to the creation statment (for example MySQL).
     * 
     * @param string $table The table name to create.
     * 
     * @param string $cols The column definitions.
     * 
     * @return string A CREATE TABLE command string.
     * 
     */
    protected function _sqlCreateTable($table, $cols)
    {
        // make sure the table name is a valid identifier
        $this->_checkIdentifier('table', $table);
        
        // array of column definitions
        $coldef = array();
        
        // use this to stack errors when creating definitions
        $err = array();
        
        // loop through each column and get its definition
        foreach ($cols as $name => $info) {
            try {
                $coldef[] = $this->_sqlColdef($name, $info);
            } catch (Solar_Sql_Exception $e) {
                throw $this->_exception('ERR_TABLE_NOT_CREATED', array(
                    'table' => $table,
                    'error' => $e->getMessage(),
                ));
                $err[$name] = array($e->getCode(), $e->getInfo());
            }
        }
        
        // no errors, build a return the CREATE statement
        $cols = implode(",\n    ", $coldef);
        $table = $this->quoteName($table);
        return "CREATE TABLE $table (\n    $cols\n)";
    }
    
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
        $this->_cache->deleteAll();
        $table = $this->quoteName($table);
        return $this->query("DROP TABLE IF EXISTS $table");
    }
    
    /**
     * 
     * Adds a portable column to a table in the database.
     * 
     * The $info parameter should be in this format ...
     * 
     * {{code: php
     *     $info = array(
     *         'type'    => (string) bool, char, int, ...
     *         'size'    => (int) total length for char|varchar|numeric
     *         'scope'   => (int) decimal places for numeric
     *         'default' => (bool) the default value, if any
     *         'require' => (bool) is the value required to be NOT NULL?
     *         'primary' => (bool) is this a primary key column?
     *         'autoinc' => (bool) is this an auto-increment column?
     *     );
     * }}
     * 
     * @param string $table The table name (1-30 chars).
     * 
     * @param string $name The column name to add (1-28 chars).
     * 
     * @param array $info Information about the column.
     * 
     * @return mixed
     * 
     */
    public function addColumn($table, $name, $info)
    {
        $this->_cache->deleteAll();
        $coldef = $this->_sqlColdef($name, $info);
        $table = $this->quoteName($table);
        $stmt = "ALTER TABLE $table ADD COLUMN $coldef";
        return $this->query($stmt);
    }
    
    /**
     * 
     * Drops a column from a table in the database.
     * 
     * @param string $table The table name.
     * 
     * @param string $name The column name to drop.
     * 
     * @return mixed
     * 
     */
    public function dropColumn($table, $name)
    {
        $this->_cache->deleteAll();
        $table = $this->quoteName($table);
        return $this->query("ALTER TABLE $table DROP COLUMN $name");
    }
    
    /**
     * 
     * Creates a portable index on a table.
     * 
     * @param string $table The name of the table for the index.
     * 
     * @param string $name The name of the index.
     * 
     * @param bool $unique Whether or not the index is unique.
     * 
     * @param array $cols The columns in the index.  If empty, uses the
     * $name parameters as the column name.
     * 
     * @return void
     * 
     */
    public function createIndex($table, $name, $unique = false, $cols = null)
    {
        // are there any columns for the index?
        if (empty($cols)) {
            // take the column name from the index name
            $cols = $name;
        }
        
        // check the table and index names
        $this->_checkIdentifier('table', $table);
        $this->_checkIdentifier('index', $name);
        
        // modify the index name as-needed
        $name = $this->_modIndexName($table, $name);
        
        // quote identifiers
        $name = $this->quoteName($name);
        $table = $this->quoteName($table);
        $cols = $this->quoteName($cols);
        
        // create a string of column names
        $cols = implode(', ', (array) $cols);
        
        // create index entry statement
        if ($unique) {
            $stmt = "CREATE UNIQUE INDEX $name ON $table ($cols)";
        } else {
            $stmt = "CREATE INDEX $name ON $table ($cols)";
        }
        return $this->query($stmt);
    }
    
    
    /**
     * 
     * Drops an index from a table in the database.
     * 
     * @param string $table The table name.
     * 
     * @param string $name The index name to drop.
     * 
     * @return mixed
     * 
     */
    public function dropIndex($table, $name)
    {
        $name = $this->_modIndexName($table, $name);
        return $this->_dropIndex($table, $name);
    }
    
    /**
     * 
     * Drops an index.
     * 
     * @param string $table The table of the index.
     * 
     * @param string $name The index name.
     * 
     * @return void
     * 
     */
    abstract protected function _dropIndex($table, $name);
    
    /**
     * 
     * Modifies an index name for adapters.
     * 
     * Most adapters don't need this, but some do (e.g. PostgreSQL and SQLite).
     * 
     * @param string $table The table on which the index occurs.
     * 
     * @param string $name The requested index name.
     * 
     * @return string The modified index name (most adapters do not modify the
     * name).
     * 
     */
    protected function _modIndexName($table, $name)
    {
        return $name;
    }
    
    /**
     * 
     * Creates a sequence in the database.
     * 
     * @param string $name The sequence name to create.
     * 
     * @param string $start The starting sequence number.
     * 
     * @return void
     * 
     * @todo Check name length.
     * 
     */
    public function createSequence($name, $start = 1)
    {
        $this->_cache->deleteAll();
        $name = $this->_modSequenceName($name);
        $result = $this->_createSequence($name, $start);
        return $result;
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
    abstract protected function _createSequence($name, $start = 1);
    
    /**
     * 
     * Drops a sequence from the database.
     * 
     * @param string $name The sequence name to drop.
     * 
     * @return void
     * 
     */
    public function dropSequence($name)
    {
        $this->_cache->deleteAll();
        $name = $this->_modSequenceName($name);
        $result = $this->_dropSequence($name);
        return $result;
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
    abstract protected function _dropSequence($name);
    
    /**
     * 
     * Modifies a sequence name for adapters.
     * 
     * Most adapters don't need this, but some do (esp. MySQL and PostgreSQL).
     * 
     * @param string $name The requested sequence name.
     * 
     * @return string The modified sequence name (most adapters do not
     * modify the name).
     * 
     */
    protected function _modSequenceName($name)
    {
        return $name;
    }
    
    
    // -----------------------------------------------------------------
    // 
    // Support
    // 
    // -----------------------------------------------------------------
    
    /**
     * 
     * Returns a column definition string.
     * 
     * The $info parameter should be in this format ...
     * 
     * {{code: php
     *     $info = array(
     *         'type'    => (string) bool, char, int, ...
     *         'size'    => (int) total length for char|varchar|numeric
     *         'scope'   => (int) decimal places for numeric
     *         'default' => (bool) the default value, if any
     *         'require' => (bool) is the value required to be NOT NULL?
     *         'primary' => (bool) is this a primary key column?
     *         'autoinc' => (bool) is this an auto-increment column?
     *     );
     * }}
     * 
     * @param string $name The column name.
     * 
     * @param array $info The column information.
     * 
     * @return string The column definition string.
     * 
     */
    protected function _sqlColdef($name, $info)
    {
        // make sure the column name is a valid identifier
        $this->_checkIdentifier('column', $name);
        
        // short-form of definition
        if (is_string($info)) {
            $info = array('type' => $info);
        }
        
        // set default values for these variables
        $tmp = array(
            'type'    => null,
            'size'    => null,
            'scope'   => null,
            'default' => null,
            'require' => null,
            'primary' => false,
            'autoinc' => false,
        );
        
        $info = array_merge($tmp, $info);
        extract($info); // see array keys, above
        
        // force values
        $name    = trim(strtolower($name));
        $type    = strtolower(trim($type));
        $size    = (int) $size;
        $scope   = (int) $scope;
        $require = (bool) $require;
        
        // is it a recognized column type?
        if (! array_key_exists($type, $this->_solar_native)) {
            throw $this->_exception('ERR_COL_TYPE', array(
                'col' => $name,
                'type' => $type,
            ));
        }
        
        // basic declaration string
        switch ($type) {
        
        case 'char':
        case 'varchar':
            // does it have a valid size?
            if ($size < 1 || $size > 255) {
                throw $this->_exception('ERR_COL_SIZE', array(
                    'col' => $name,
                    'size' => $size,
                ));
            } else {
                // replace the 'size' placeholder
                $coldef = $this->_solar_native[$type] . "($size)";
            }
            break;
        
        case 'numeric':
        
            if ($size < 1 || $size > 255) {
                throw $this->_exception('ERR_COL_SIZE', array(
                    'col' => $name,
                    'size' => $size,
                    'scope' => $scope,
                ));
            }
            
            if ($scope < 0 || $scope > $size) {
                throw $this->_exception('ERR_COL_SCOPE', array(
                    'col' => $name,
                    'size' => $size,
                    'scope' => $scope,
                ));
            }
            
            // replace the 'size' and 'scope' placeholders
            $coldef = $this->_solar_native[$type] . "($size,$scope)";
            
            break;
        
        default:
            $coldef = $this->_solar_native[$type];
            break;
        
        }
        
        // set the "NULL"/"NOT NULL" portion
        $coldef .= ($require) ? ' NOT NULL' : ' NULL';
        
        // set the default value, if any.
        // use isset() to allow for '0' and '' values.
        if (isset($default)) {
            $coldef .= ' DEFAULT ' . $this->quote($default);
        }
        
        // modify with auto-increment and primary-key portions
        $this->_modAutoincPrimary($coldef, $autoinc, $primary);
        
        // done
        $name = $this->quoteName($name);
        return "$name $coldef";
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
    abstract protected function _modAutoincPrimary(&$coldef, $autoinc, $primary);
    
    /**
     * 
     * Check if a table, index, or column name is a valid portable identifier.
     * Throws an exception on failure.
     * 
     * @param string $type The indentifier type: table, index, sequence, etc.
     * 
     * @param string $name The identifier name to check.
     * 
     * @return void
     * 
     */
    protected function _checkIdentifier($type, $name)
    {
        if ($type == 'column') {
            $this->_checkIdentifierColumn($name);
        } else {
            list($schema, $ident) = $this->_splitSchemaIdent($name);
            if ($schema) {
                $this->_checkIdentifierPart($type, $name, $schema);
            }
            $this->_checkIdentifierPart($type, $name, $ident);
        }
    }
    
    /**
     * 
     * Checks one part of a dotted identifier (schema.table, database.table,
     * etc).  Throws an exception on failure.
     * 
     * @param string $type The identifier type (table, index, etc).
     * 
     * @param string $name The full identifier name (with dots, if any).
     * 
     * @param string $part The part of the name that we're checking.
     * 
     * @return void
     * 
     */
    protected function _checkIdentifierPart($type, $name, $part)
    {
        // validate identifier length
        $len = strlen($part);
        if ($len < 1 || $len > $this->_maxlen) {
            throw $this->_exception('ERR_IDENTIFIER_LENGTH', array(
                'type' => $type,
                'name' => $name,
                'part' => $part,
                'min'  => 1,
                'max'  => $this->_maxlen,
                'len'  => $len,
            ));
        }
        
        // only a-z, 0-9, and _ are allowed in words.
        // must start with a letter, not a number or underscore.
        $regex = '/^[a-z][a-z0-9_]*$/';
        if (! preg_match($regex, $name)) {
            throw $this->_exception('ERR_IDENTIFIER_CHARS', array(
                'type'  => $type,
                'name'  => $name,
                'part'  => $part,
                'regex' => $regex,
            ));
        }
    }
    
    /**
     * 
     * Checks a column name.
     * 
     * @param string $name The column name.
     * 
     * @return void
     * 
     */
    protected function _checkIdentifierColumn($name)
    {
        $this->_checkIdentifierPart('column', $name, $name);
        
        // also, must not have two or more underscores in a row
        if (strpos($name, '__') !== false) {
            throw $this->_exception('ERR_IDENTIFIER_UNDERSCORES', array(
                'type'  => 'column',
                'name'  => $name,
            ));
        }
    }
    
    /**
     * 
     * Splits a `schema.table` identifier into its component parts.
     * 
     * @param string $spec The `table` or `schema.table` identifier.
     * 
     * @return array A sequential array where element 0 is the schema and
     * element 1 is the table name.
     * 
     */
    protected function _splitSchemaIdent($spec)
    {
        $pos = strpos($spec, '.');
        if ($pos !== false) {
            $schema = substr($spec, 0, $pos);
            $ident  = substr($spec, $pos + 1);
        } else {
            $schema = null;
            $ident  = $spec;
        }
        return array($schema, $ident);
    }
}
