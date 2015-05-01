<?php
/**
 * This file is part of the PPI Framework.
 *
 * @copyright  Copyright (c) 2012 Paul Dragoonis <paul@ppi.io>
 * @license    http://opensource.org/licenses/mit-license.php MIT
 *
 * @link       http://www.ppi.io
 */

namespace PPI\Framework\Module;

/**
 * ModuleInterface.
 *
 * @author     Paul Dragoonis <paul@ppi.io>
 * @author      Vítor Brandão <vitor@ppi.io>
 *
 * @api
 */
interface ModuleInterface
{
    /**
     * Returns the module name.
     *
     * @return string The Module name
     */
    public function getName();

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
