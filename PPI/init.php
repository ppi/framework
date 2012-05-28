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
require PPI_VENDOR_PATH . 'Symfony/Component/ClassLoader/MapClassLoader.php';
require PPI_VENDOR_PATH . 'Symfony/Component/ClassLoader/UniversalClassLoader.php';
use PPI\Autoload;

$mapLoader = new \Symfony\Component\ClassLoader\MapClassLoader(include_once(PPI_PATH . 'autoload_classmap.php'));
$mapLoader->register();

Autoload::config(array(
	'loader'    => new \Symfony\Component\ClassLoader\UniversalClassLoader(),
	'mapLoader' => $mapLoader
));
Autoload::add('PPI', dirname(PPI_PATH));
Autoload::add('Symfony', PPI_VENDOR_PATH);
Autoload::add('Zend', PPI_VENDOR_PATH);
Autoload::add('Doctrine', PPI_VENDOR_PATH);
Autoload::register();