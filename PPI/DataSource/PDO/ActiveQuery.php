<?php
/**
 * This file is part of the PPI Framework.
 *
 * @copyright  Copyright (c) 2012 Paul Dragoonis <paul@ppi.io>
 * @license    http://opensource.org/licenses/mit-license.php MIT
 * @link       http://www.ppi.io
 */
namespace PPI\DataSource\PDO;

/**
 * ActiveQuery class
 *
 * @todo Add inline documentation.
 *
 * @package    PPI
 * @subpackage DataSource
 */
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

    /**
     * Constructor.
     *
     * @param array $options
     *
     * @return void
     */
    public function __construct(array $options = array())
    {
        // Setup our connection from the key passed to meta['conn']
        if (isset($options['meta'])) {
            $this->_meta = $options['meta'];
        }

        $this->_options = $options;
    }

    /**
     * @todo Add inline documentation.
     *
     * @param type $conn
     *
     * @return void
     */
    public function setConn($conn)
    {
        $this->_conn = $conn;
    }

    /**
     * @todo Add inline documentation.
     *
     * @return type
     */
    public function fetchAll()
    {
        return $this->_conn->query(sprintf(
            'SELECT * FROM %s', $this->_meta['table']
        ))->fetchAll();
    }

    /**
     * Find a row by primary key
     *
     * @param string $id
     *
     * @return array|false
     */
    public function find($id)
    {
        return $this->_conn->fetchAssoc(sprintf(
            'SELECT * FROM %s WHERE %s = ?',
            $this->_meta['table'],
            $this->_meta['primary']
        ), array($id));
    }

    /**
     * @todo Add inline documentation.
     *
     * @param array $data
     *
     * @return type
     */
    public function insert(array $data)
    {
        $this->_conn->insert($this->_meta['table'], $data);

        return $this->_conn->lastInsertId();
    }

    /**
     * @todo Add inline documentation.
     *
     * @param type $where
     *
     * @return type
     */
    public function delete($where)
    {
        return $this->_conn->delete($this->_meta['table'], $where);
    }

    /**
     * @todo Add inline documentation.
     *
     * @param array $data
     * @param type  $where
     *
     * @return type
     */
    public function update(array $data, $where)
    {
        return $this->_conn->update($this->_meta['table'], $data, $where);
    }

}
