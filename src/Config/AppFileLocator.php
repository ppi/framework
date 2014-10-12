<?php
/**
 * This file is part of the PPI Framework.
 *
 * @copyright  Copyright (c) 2012 Paul Dragoonis <paul@ppi.io>
 * @license    http://opensource.org/licenses/mit-license.php MIT
 * @link       http://www.ppi.io
 */

namespace PPI\Config;

use PPI\Module\ModuleManager;

/**
 * AppFileLocator uses ModuleManager to locate resources in modules.
 *
 * @author     Paul Dragoonis <paul@ppi.io>
 * @author     Vítor Brandão <vitor@ppi.io>
 * @package    PPI
 * @subpackage Config
 */
class AppFileLocator extends FileLocator
{
    protected $moduleManager;
    protected $path;

    /**
     * Constructor.
     *
     * @param ModuleManager $moduleManager A ModuleManager instance
     * @param null|string   $path          The path the global resource directory
     * @param array         $paths         An array of paths where to look for resources
     */
    public function __construct(ModuleManager $moduleManager, $path = null, array $paths = array())
    {
        $this->moduleManager = $moduleManager;
        if (null !== $path) {
            $this->path = $path;
            $paths[] = $path;
        }

        parent::__construct($paths);
    }

    /**
     * {@inheritdoc}
     */
    public function locate($file, $currentPath = null, $first = true)
    {
        if ('@' === $file[0]) {
            return $this->moduleManager->locateResource($file, $this->path, $first);
        }

        return parent::locate($file, $currentPath, $first);
    }

    /**
     * Returns the path to the app directory.
     *
     * @return string The path to the app directory.
     */
    public function getAppPath()
    {
        return $this->path;
    }

    /**
     * Returns an array of paths to modules.
     *
     * @return array An array of paths to each loaded module
     */
    public function getModulesPath()
    {
        $paths = array();
        foreach ($this->moduleManager->getLoadedModules(true) as $module) {
            $paths[$module->getName()] = $module->getPath();
        }

        return $paths;
    }
}
