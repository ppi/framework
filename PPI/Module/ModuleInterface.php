<?php
/**
 * This file is part of the PPI Framework.
 *
 * @copyright  Copyright (c) 2012 Paul Dragoonis <paul@ppi.io>
 * @license    http://opensource.org/licenses/mit-license.php MIT
 * @link       http://www.ppi.io
 */
namespace PPI\Module;

/**
 * ModuleInterface.
 *
 * @author      Vítor Brandão <vitor@ppi.io>
 * @package     PPI
 * @subpackage  Module
 *
 * @api
 */
interface ModuleInterface
{
    /**
     * Get the module name
     *
     * @return string
     */
    public function getModuleName();

    /**
     * Gets the Module namespace.
     *
     * @return string The Module namespace
     *
     * @api
     */
    public function getNamespace();

    /**
     * Gets the Module directory path.
     *
     * The path should always be returned as a Unix path (with /).
     *
     * @return string The Module absolute path
     *
     * @api
     */
    public function getPath();
}
