<?php
/**
 * 
 * Session adapter for SQL based data store.
 * 
 * @category Solar
 * 
 * @package Solar_Session
 * 
 * @author Antti Holvikari <anttih@gmail.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Sql.php 3850 2009-06-24 20:18:27Z pmjones $
 * 
 */
class Solar_Session_Handler_Adapter_Sql extends Solar_Session_Handler_Adapter
{
    /**
     * 
     * Default configuration values.
     * 
     * @config dependency sql A Solar_Sql dependency injection.
     * 
     * @config string table Table where the session data will be stored, default
     * 'sessions'.
     * 
     * @config string created_col Column name where time of creation is to be 
     * stored, default 'created'.
     * 
     * @config string updated_col Column name where time of update is to be 
     * stored, default 'updated'.
     * 
     * @config string id_col Column name of the session id, default 'id'.
     * 
     * @config string data_col Column name where the actual session data will 
     * be stored, default 'data'.
     * 
     * @var array
     * 
     */
    protected $_Solar_Session_Handler_Adapter_Sql = array(
        'sql'         => 'sql',
        'table'       => 'sessions',
        'id_col'      => 'id',
        'created_col' => 'created',
        'updated_col' => 'updated',
        'data_col'    => 'data',
    );
    
    /**
     * 
     * Solar_Sql object to connect to the database.
     * 
     * @var Solar_Sql_Adapter
     * 
     */
    protected $_sql;
    
    /**
     * 
     * Open session handler.
     * 
     * @return bool
     * 
     */
    public function open()
    {
        if (! $this->_sql) {
            $this->_sql = Solar::dependency(
                'Solar_Sql',
                $this->_config['sql']
            );
        }
        
        return true;
    }
    
    /**
     * 
     * Close session handler.
     * 
     * @return bool
     * 
     */
    public function close()
    {
        $this->_sql->disconnect();
        $this->_sql = null;
        return true;
    }
    
    /**
     * 
     * Reads session data.
     * 
     * @param string $id The session ID.
     * 
     * @return string The serialized session data.
     * 
     */
    public function read($id)
    {
        $sel = Solar::factory(
            'Solar_Sql_Select',
            array('sql' => $this->_sql)
        );
        
        $sel->from($this->_config['table'])
            ->cols($this->_config['data_col'])
            ->where("{$this->_config['id_col']} = ?", $id);
        
        return $sel->fetchValue();
    }
    
    /**
     * 
     * Writes session data.
     * 
     * @param string $id The session ID.
     * 
     * @param string $data The serialized session data.
     * 
     * @return bool
     * 
     */
    public function write($id, $data)
    {
        $sel = Solar::factory(
            'Solar_Sql_Select',
            array('sql' => $this->_sql)
        );
        
        // select up to 2 records from the database
        $sel->from($this->_config['table'])
            ->cols($this->_config['id_col'])
            ->where("{$this->_config['id_col']} = ?", $id)
            ->limit(2);
            
        // use fetchCol() instead of countPages() for speed reasons.
        // count on some DBs is pretty slow, so this will fetch only
        // the rows we need.
        $rows  = $sel->fetchCol();
        $count = count((array) $rows);
        
        // insert or update?
        if ($count == 0) {
            // no data yet, insert
            return $this->_insert($id, $data);
        } elseif ($count == 1) {
            // existing data, update
            return $this->_update($id, $data);
        } else {
            // more than one row means an ID collision
            // @todo log this somehow?
            return false;
        }
    }
    
    /**
     * 
     * Destroys session data.
     * 
     * @param string $id The session ID.
     * 
     * @return bool
     * 
     */
    public function destroy($id)
    {
        $this->_sql->delete(
            $this->_config['table'],
            array("{$this->_config['id_col']} = ?" => $id)
        );
        
        return true;
    }
    
    /**
     * 
     * Removes old session data (garbage collection).
     * 
     * @param int $lifetime Removes session data not updated since this many
     * seconds ago.  E.g., a lifetime of 86400 removes all session data not
     * updated in the past 24 hours.
     * 
     * @return bool
     * 
     */
    public function gc($lifetime)
    {
        // timestamp is current time minus session.gc_maxlifetime
        $timestamp = date(
            'Y-m-d H:i:s',
            mktime(date('H'), date('i'), date('s') - $lifetime)
        );
        
        // delete all sessions last updated before the timestamp
        $this->_sql->delete($this->_config['table'], array(
            "{$this->_config['updated_col']} < ?" => $timestamp,
        ));
        
        return true;
    }
    
    /**
     * 
     * Inserts a new session-data row in the database.
     * 
     * @param string $id The session ID.
     * 
     * @param string $data The serialized session data.
     * 
     * @return bool
     * 
     */
    protected function _insert($id, $data)
    {
        $now = date('Y-m-d H:i:s');
        
        $cols = array(
            $this->_config['created_col'] => $now,
            $this->_config['updated_col'] => $now,
            $this->_config['id_col']      => $id,
            $this->_config['data_col']    => $data,
        );
        
        try {
            $this->_sql->insert($this->_config['table'], $cols);
            return true;
        } catch (Solar_Sql_Exception $e) {
            // @todo log this somehow?
            return false;
        }
    }
    
    /**
     * 
     * Updates an existing session-data row in the database.
     * 
     * @param string $id The session ID.
     * 
     * @param string $data The serialized session data.
     * 
     * @return bool
     * 
     * @todo Should we log caught exceptions?
     *
     */
    protected function _update($id, $data)
    {
        $cols = array(
            $this->_config['updated_col'] => date('Y-m-d H:i:s'),
            $this->_config['data_col']    => $data,
        );
        
        $where = array("{$this->_config['id_col']} = ?" => $id);
        
        try {
            $this->_sql->update($this->_config['table'], $cols, $where);
            return true;
        } catch (Solar_Sql_Exception $e) {
            // @todo log this somehow?
            return false;
        }
    }
}