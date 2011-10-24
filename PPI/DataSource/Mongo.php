<?php
/**
 * @author    Paul Dragoonis <dragoonis@php.net>
 * @license   http://opensource.org/licenses/mit-license.php MIT
 * @package   DataSource
 * @link      www.ppiframework.com
 */
namespace PPI\DataSource;
class Mongo {
	
    protected $conn = array();

	function __construct() {

	}

	function getDriver(array $config) {
		
		if (!class_exists('Mongo')) {
			throw new PPI_Exception('Mongo extension is missing');
		}

		if(!isset($config['username'], $config['password'], $config['hostname'])) {
			throw new PPI_Exception('Missing connection properties. Make sure you enter a username, password and hostname');
		}
		
		$dsn = 'mongodb://' . "{$config['username']}:{$config['password']}@{$config['hostname']}";
		
		if(isset($config['port'])) {
			$dsn .= ":{$config['port']}";
		}
		
		if(isset($config['database'])) {
			$dsn .= "/{$config['database']}";
		}
		
		if(!isset($config['options'])) {
			$config['options'] = array();
		}

        return new \Mongo($dsn, $config['options']);
    }

}
