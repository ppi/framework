<?php
/**
 * This file is part of the PPI Framework.
 *
 * @copyright  Copyright (c) 2012 Paul Dragoonis <paul@ppi.io>
 * @license    http://opensource.org/licenses/mit-license.php MIT
 * @link       http://www.ppi.io
 */
namespace PPI\DataSource;

/**
 * @todo Add inline documentation.
 *
 * @package    PPI
 * @subpackage DataSource
 */
interface DataSourceInterface
{
    /**
     * Manufacturs datasource drivers.
     *
     * @param string $key
     *
     * @return object
     *
     * @throws DataSourceException
     */
    public function factory(array $options);

    /**
     * Create a new instance of the DataSourceInterface implementor.
     *
     * @param array $options
     *
     * @return DataSourceInterface
     *
     * @static
     */
    public static function create(array $options = array());

    /**
     * Returns the connection from the factory.
     *
     * @param string $key
     *
     * @return object
     *
     * @throws DataSourceException
     */
    public function getConnection($key);

    /**
     * Returns the connection configuration options for a specified key.
     *
     * @param string $key
     *
     * @return array
     */
    public function getConnectionConfig($key);

}
