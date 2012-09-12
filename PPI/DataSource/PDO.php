<?php
/**
 * This file is part of the PPI Framework.
 *
 * @copyright  Copyright (c) 2012 Paul Dragoonis <paul@ppi.io>
 * @license    http://opensource.org/licenses/mit-license.php MIT
 * @link       http://www.ppi.io
 */
namespace PPI\DataSource;

use Doctrine\DBAL\DriverManager,
    Doctrine\DBAL\Configuration;

/**
 * PDO class
 *
 * @todo Add inline documentation.
 *
 * @package    PPI
 * @subpackage DataSource
 */
class PDO
{
    /**
     * Constructor.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * @todo Add inline documentation.
     *
     * @param array $config
     *
     * @return type
     */
    public function getDriver(array $config)
    {
        $connObject = new Configuration();

        // We map our config options to Doctrine's naming of them
        $connParamsMap = array(
            'database' => 'dbname',
            'username' => 'user',
            'hostname' => 'host',
            'pass'     => 'password'
        );

        foreach ($connParamsMap as $key => $param) {
            if (isset($config[$key])) {
                $config[$param] = $config[$key];
                unset($config[$key]);
            }
        }

        $config['driver'] = $config['type'];
        unset($config['type']);

        return DriverManager::getConnection($config, $connObject);
    }

}
