<?php
/**
 * @author    Paul Dragoonis <dragoonis@php.net>
 * @license   http://opensource.org/licenses/mit-license.php MIT
 * @package   DataSource
 * @link      www.ppi.io
 */
namespace PPI\DataSource;
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

    public function __construct(array $options = array())
    {
        // Setup our connection from the key passed to meta['conn']
        if (isset($this->_meta['conn'])) {

            $dsConfig = \PPI\Core::getDataSource()->getConnectionConfig($this->_meta['conn']);
            $connType = $dsConfig['type'];
            if ($connType === 'mongo') {
                $this->_handler = new \PPI\DataSource\Mongo\ActiveQuery(array(
                    'meta' => $this->_meta
                ));
            } elseif (substr($connType, 0, 3) === 'pdo') {
                $this->_handler = new \PPI\DataSource\PDO\ActiveQuery(array(
                    'meta' => $this->_meta
                ));
            }
            $this->_conn = \PPI\Core::getDataSourceConnection($this->_meta['conn']);
            $this->_handler->setConn($this->_conn);
        }

        $this->_options = $options;
    }

    /**
     * Fetch all rows based on the $criteria
     *
     * @param  null|object $criteria
     * @return mixed
     */
    public function fetchAll($criteria = null)
    {
        return $this->_handler->fetchAll($criteria);
    }

    /**
     * Find a row by its primary key
     *
     * @param  string $id
     * @return mixed
     */
    public function find($id)
    {
        return $this->_handler->find($id);
    }

    /**
     * Fetch records from the datasource by a $where clause
     *
     * @param  array $where
     * @param  array $params
     * @return mixed
     */
    public function fetch(array $where, array $params = array())
    {
        return $this->_handler->fetch($where, $params);
    }

    /**
     * Insert data into the table
     *
     * @param $data
     * @return mixed
     */
    public function insert($data)
    {
        return $this->_handler->insert($data);
    }

    /**
     * Delete a record by a where clause
     *
     * @param  array $where
     * @return mixed
     */
    public function delete($where)
    {
        return $this->_handler->delete($where);
    }

    /**
     * Update a record by where clause
     *
     * @param  array $data  The fields and values
     * @param  array $where The clause
     * @return mixed
     */
    public function update($data, $where)
    {
        return $this->_handler->update($data, $where);
    }

}
