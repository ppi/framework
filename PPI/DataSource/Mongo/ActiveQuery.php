<?php
/**
 * @author    Paul Dragoonis <dragoonis@php.net>
 * @license   http://opensource.org/licenses/mit-license.php MIT
 * @package   DataSource
 * @link      www.ppiframework.com
 */
namespace PPI\DataSource\Mongo;
class ActiveQuery
{
    /**
     * The table name
     *
     * @var null
     */
    protected $_table = null;

    /**
     * The primary key
     *
     * @var null
     */
    protected $_primary = null;

    /**
     * The datasource connection
     *
     * @var null
     */
    protected $_conn = null;

    /**
     * The meta data for this instantiation
     *
     * @var array
    */
    protected $_meta = array(
        'conn'    => null,
        'table'   => null,
        'primary' => null
    );

    /**
     * The options for this instantiation
     *
     * @var array
    */
    protected $_options = array();

    public function __construct(array $options = array())
    {
        // Setup our connection from the key passed to meta['conn']
        if (isset($options['meta'])) {
            $this->_conn = \PPI\Core::getDataSourceConnection($this->_meta['conn']);
        }

        $this->_options = $options;
    }

    public function setConn($conn)
    {
        $this->_conn = $conn;
    }

    public function fetchAll()
    {
        return $this->_conn->query("SELECT * FROM {$this->_meta['table']}")->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function find($id)
    {
        return $this->_conn->fetchAssoc("SELECT * FROM {$this->_meta['table']} WHERE {$this->_meta['primary']} = ?", array($id));
    }

    public function fetch(array $where, array $params = array())
    {
        die("SELECT * FROM {$this->_meta['table']} WHERE $where");

        return $this->_conn->fetchAssoc("SELECT * FROM {$this->_meta['table']} WHERE $where", $params);
    }

    public function insert($data)
    {
        $this->_conn->insert($this->_table, $data);

        return $this->_conn->lastInsertId();
    }

    public function delete($where)
    {
        return $this->_conn->delete($this->_table, $where);
    }

    public function update($data, $where)
    {
        return $this->_conn->update($this->_table, $data, $where);
    }

}
