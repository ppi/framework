<?php

/**
 * @author    Paul Dragoonis <dragoonis@php.net>
 * @license   http://opensource.org/licenses/mit-license.php MIT
 * @package   PPI
 * @link      www.ppi.io
 */
// ---- site wide -----
defined('DS')					|| define('DS', DIRECTORY_SEPARATOR);
defined('ROOTPATH')				|| define('ROOTPATH', getcwd() . DS);
defined('SYSTEMPATH')			|| define('SYSTEMPATH', dirname(__FILE__) . DS);
defined('BASEPATH')				|| define('BASEPATH', dirname(__FILE__) . DS);
defined('TESTPATH')             || define('TESTPATH', SYSTEMPATH . 'Test' . DS);
defined('APPFOLDER')			|| define('APPFOLDER', ROOTPATH . 'App' . DS);
defined('VENDORPATH')           || define('VENDORPATH', dirname(SYSTEMPATH) . '/Vendor' . DS);

// ---- app paths ------
defined('MODELPATH')			|| define('MODELPATH', APPFOLDER . 'Model' . DS);
defined('VIEWPATH')				|| define('VIEWPATH', APPFOLDER . 'View' . DS);
defined('CONTROLLERPATH')		|| define('CONTROLLERPATH', APPFOLDER . 'Controller' . DS);
defined('CONFIGPATH')			|| define('CONFIGPATH', APPFOLDER . 'Config' . DS);

// ------- system constants -------
defined('PPI_VERSION')			|| define('PPI_VERSION', '1.1');

// Autoload registration
require 'Autoload.php';
PPI\Autoload::add('PPI', array('path' => dirname(SYSTEMPATH)));
PPI\Autoload::add('App', array('path' => APPFOLDER));
PPI\Autoload::register();

// General stuff
//require 'common.php';
// load up custom error handlers
//require 'errors.php';
//setErrorHandlers('ppi_error_handler', 'ppi_exception_handler');