<?php
/**
 * This file is part of the PPI Framework.
 *
 * @copyright  Copyright (c) 2012 Paul Dragoonis <paul@ppi.io>
 * @license    http://opensource.org/licenses/mit-license.php MIT
 * @link       http://www.ppi.io
 */
namespace PPI\DataSource;

use PPI\DataSource\DataSourceInterface;

/**
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
    protected $_handler = null;

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
     * Optionally pass in a DataSource
     *
     * @param null|object
     *
     * @return void
     */
    public function __construct($dataSource = null)
    {
        if ($dataSource !== null) {
            $this->setDataSource($dataSource);
        }
    }

    /**
     * Set the datasource service into this class
     *
     * @param \PPI\DataSource\DataSourceInterface $dataSource
     *
     * @return void
     */
    public function setDataSource(DataSourceInterface $dataSource)
    {
        // Setup our connection from the key passed to meta['conn']
        if ($this->getConnectionName() !== null && $this->getTableName() !== null) {

            $dsConfig          = $dataSource->getConnectionConfig($this->getConnectionName());
            $this->_conn       = $dataSource->getConnection($this->getConnectionName());
            $this->_dataSource = $dataSource;

            if (isset($dsConfig['type']) && substr($dsConfig['type'], 0, 3) === 'pdo') {
                $this->_handler = $dataSource->activeQueryFactory('pdo', array('meta' => $this->_meta));
                $this->_handler->setConn($this->_conn);
            }
        }
    }

    /**
     * Fetch all rows based on the $criteria
     *
     * @param null|object $criteria
     *
     * @return mixed
     */
    public function fetchAll($criteria = null)
    {
        return $this->_handler->fetchAll($criteria);
    }

    /**
     * Find a row by its primary key
     *
     * @param string $id
     *
     * @return mixed
     */
    public function find($id)
    {
        return $this->_handler->find($id);
    }

    /**
     * Fetch records from the datasource by a $where clause
     *
     * @param array $where
     * @param array $params
     *
     * @return mixed
     */
    public function fetch(array $where, array $params = array())
    {
        return $this->_handler->fetch($where, $params);
    }

    /**
     * Insert data into the table
     *
     * @param mixed $data
     *
     * @return mixed
     */
    public function insert($data)
    {
        return $this->_handler->insert($data);
    }

    /**
     * Delete a record by a where clause
     *
     * @param array $where
     *
     * @return mixed
     */
    public function delete($where)
    {
        return $this->_handler->delete($where);
    }

    /**
     * Update a record by where clause
     *
     * @param array $data  The fields and values
     * @param array $where The clause
     *
     * @return mixed
     */
    public function update($data, $where)
    {
        return $this->_handler->update($data, $where);
    }

    /**
     * Get the connection name
     *
     * @return string
     */
    protected function getConnectionName()
    {
        return isset($this->_meta['conn']) ? $this->_meta['conn'] : null;
    }

    /**
     * Get the storage class' table name
     *
     * @return string
     */
    protected function getTableName()
    {
        return isset($this->_meta['table']) ? $this->_meta['table'] : null;
    }

    /**
     * Get the primary key for this storage class' connection
     *
     * @return string
     */
    protected function getPrimaryKey()
    {
        return isset($this->_meta['primary']) ? $this->_meta['primary'] : null;
    }

    /**
     * Get the fetch mode of the active query instance.
     * i.e: \PDO::FETCH_ASSOC
     *
     * @return null
     */
    protected function getFetchMode()
    {
        return isset($this->_meta['fetchMode']) ? $this->_meta['fetchMode'] : null;
    }

    /**
     * Create the query builder object
     *
     * @return mixed
     */
    protected function createQueryBuilder()
    {
        return $this->getConnection()->createQueryBuilder();
    }

    /**
     * Get the connection class
     *
     * @return mixed
     */
    protected function getConnection()
    {
        return $this->_conn;
    }

    /**
     * Get the connection options
     *
     * @return array
     */
    protected function getConnectionOptions()
    {
        return isset($this->_meta) ? $this->_meta : array();
    }

}
