<?php
/**
 * This file is part of the PPI Framework.
 *
 * @copyright  Copyright (c) 2012-2015 Paul Dragoonis <paul@ppi.io>
 * @license    http://opensource.org/licenses/mit-license.php MIT
 * @link       http://www.ppi.io
 */

namespace PPI\Framework;

/**
 * The PPI App interface.
 *
 * This class sets various app settings, and allows you to override classes used
 * in the bootup process.
 *
 * @author     Vítor Brandão <vitor@ppi.io>
 * @package    PPI
 * @subpackage Core
 *
 * @api
 */
interface AppInterface extends \Serializable
{
    /**
     * Gets the name of the application.
     *
     * @return string The application name
     *
     * @api
     */
    public function getName();

    /**
     * Gets the version of the application.
     *
     * @return string The application version
     *
     * @api
     */
    public function getVersion();

    /**
     * Get the environment mode the application is in.
     *
     * @return string The current environment
     *
     * @api
     */
    public function getEnvironment();

    /**
     * Checks if debug mode is enabled.
     *
     * @return Boolean true if debug mode is enabled, false otherwise
     *
     * @api
     */
    public function isDebug();

    /**
     * Gets the application root dir.
     *
     * @return string The application root dir
     *
     * @api
     */
    public function getRootDir();

    /**
     * Get the locator object
     *
     * @return \Zend\ServiceManager\ServiceLocatorInterface
     *
     * @api
     */
    public function getServiceManager();

    /**
     * Gets the cache directory.
     *
     * @return string The cache directory
     *
     * @api
     */
    public function getCacheDir();

    /**
     * Gets the log directory.
     *
     * @return string The log directory
     *
     * @api
     */
    public function getLogDir();

    /**
     * Gets the charset of the application.
     *
     * @return string The charset
     *
     * @api
     */
    public function getCharset();
}
