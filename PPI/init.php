<?php

/**
 * @author    Paul Dragoonis <dragoonis@php.net>
 * @license   http://opensource.org/licenses/mit-license.php MIT
 * @package   PPI
 * @link      www.ppi.io
 */
defined('PPI_VERSION') || define('PPI_VERSION', '2.0');
defined('DS') || define('DS', DIRECTORY_SEPARATOR);
defined('PPI_PATH') || define('PPI_PATH', realpath(__DIR__));
defined('PPI_VENDOR_PATH') || define('PPI_VENDOR_PATH', dirname(PPI_PATH) . DS . 'Vendor' . DS);

// Autoload registration
require 'Autoload.php';
PPI\Autoload::add('PPI', dirname(PPI_PATH));
PPI\Autoload::add('Symfony', dirname(PPI_VENDOR_PATH . 'Symfony'));
PPI\Autoload::register();


// General stuff
//require 'common.php';
// load up custom error handlers
//require 'errors.php';
//setErrorHandlers('ppi_error_handler', 'ppi_exception_handler');