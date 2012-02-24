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
use PPI\Autoload;

Autoload::config(array(
	'loaderClassPath' => PPI_VENDOR_PATH . 'Symfony/Component/ClassLoader/UniversalClassLoader.php',
	'loaderClass'     => '\Symfony\Component\ClassLoader\UniversalClassLoader'
));
Autoload::add('PPI', dirname(PPI_PATH));
Autoload::add('Symfony', dirname(PPI_VENDOR_PATH . 'Symfony'));
Autoload::add('Zend', dirname(PPI_VENDOR_PATH . 'Zend'));
Autoload::register();


// General stuff
//require 'common.php';
// load up custom error handlers
//require 'errors.php';
//setErrorHandlers('ppi_error_handler', 'ppi_exception_handler');
