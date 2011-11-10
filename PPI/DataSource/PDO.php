<?php
namespace PPI\DataSource;

use Doctrine\Common\ClassLoader,
	Doctrine\DBAL\DriverManager,
	Doctrine\DBAL\Configuration,
	PPI\Autoload;
/**
 * @author    Paul Dragoonis <dragoonis@php.net>
 * @license   http://opensource.org/licenses/mit-license.php MIT
 * @package   DataSource
 * @link      www.ppi.io
 */
class PDO {

	function __construct() {

	}

	function getDriver(array $config) {

		$connObject = new Configuration();

		// We map our config options to Doctrine's naming of them
		$connParamsMap = array(
			'database' => 'dbname',
			'username' => 'user',
			'hostname' => 'host',
			'pass'     => 'password'
		);

		foreach($connParamsMap as $key => $param) {
			if(isset($config[$key])) {
				$config[$param] = $config[$key];
				unset($config[$key]);
			}
		}

		$config['driver'] = $config['type'];
		unset($config['type']);

		return DriverManager::getConnection($config, $connObject);

	}

}