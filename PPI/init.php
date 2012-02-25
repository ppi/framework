<?php

/**
 * @author    Paul Dragoonis <dragoonis@php.net>
 * @license   http://opensource.org/licenses/mit-license.php MIT
 * @package   PPI
 * @link      www.ppi.io
 */
defined('PPI_VERSION') || define('PPI_VERSION', '2.0');
defined('DS') || define('DS', DIRECTORY_SEPARATOR);
defined('PPI_PATH') || define('PPI_PATH', realpath(__DIR__) . '/');
defined('PPI_VENDOR_PATH') || define('PPI_VENDOR_PATH', dirname(PPI_PATH) . '/Vendor/');

// Autoload registration
require 'Autoload.php';
require PPI_VENDOR_PATH . 'Zend/Loader/ClassMapAutoloader.php';
require PPI_VENDOR_PATH . 'Zend/Loader/StandardAutoloader.php';
use PPI\Autoload;

$mapLoader = new \Zend\Loader\ClassMapAutoloader();
$mapLoader->registerAutoloadMap(PPI_PATH . 'autoload_classmap.php');
$mapLoader->register();

Autoload::config(array(
	'loader'    => new \Zend\Loader\StandardAutoloader(),
	'mapLoader' => $mapLoader
));
Autoload::add('PPI', PPI_PATH);
Autoload::add('Symfony', PPI_VENDOR_PATH . 'Symfony');
Autoload::register();