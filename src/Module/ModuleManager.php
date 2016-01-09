<?php
/**
 * This file is part of the PPI Framework.
 *
 * @copyright  Copyright (c) 2011-2013 Paul Dragoonis <paul@ppi.io>
 * @license    http://opensource.org/licenses/mit-license.php MIT
 *
 * @link       http://www.ppi.io
 */

namespace PPI\Framework\Module;

use Zend\ModuleManager\ModuleManager as BaseModuleManager;

/**
 * ModuleManager.
 *
 * @author     Paul Dragoonis <paul@ppi.io>
 * @author     Vítor Brandão <vitor@ppi.io>
 */
class ModuleManager extends BaseModuleManager
{
    /**
     * Returns an array of paths to modules.
     *
     * @return array An array of paths to each loaded module
     */
    public function getModulesPath()
    {
        $paths = array();
        foreach ($this->getLoadedModules(true) as $module) {
            $paths[$module->getName()] = $module->getPath();
        }

        return $paths;
    }

    /**
     * Returns the file path for a given resource.
     *
     * A Resource can be a file or a directory.
     *
     * The resource name must follow the following pattern:
     *
     *     @<ModuleName>/path/to/a/file.something
     *
     * where ModuleName is the name of the module
     * and the remaining part is the relative path in the module.
     *
     * If $dir is passed, and the first segment of the path is "Resources",
     * this method will look for a file named:
     *
     *     $dir/<ModuleName>/path/without/Resources
     *
     * before looking in the module resource folder.
     *
     * @param string $name  A resource name to locate
     * @param string $dir   A directory where to look for the resource first
     * @param bool   $first Whether to return the first path or paths for all matching modules
     *
     * @throws \InvalidArgumentException if the file cannot be found or the name is not valid
     * @throws \RuntimeException         if the name contains invalid/unsafe
     * @throws \RuntimeException         if a custom resource is hidden by a resource in a derived module
     *
     * @return string|array The absolute path of the resource or an array if $first is false
     *
     * @api
     */
    public function locateResource($name, $dir = null, $first = true)
    {
        if ('@' !== $name[0]) {
            throw new \InvalidArgumentException(sprintf('A resource name must start with @ ("%s" given).', $name));
        }

        if (false !== strpos($name, '..')) {
            throw new \RuntimeException(sprintf('File name "%s" contains invalid characters (..).', $name));
        }
        $moduleName = substr($name, 1);
        $path       = '';
        if (false !== strpos($moduleName, '/')) {
            list($moduleName, $path) = explode('/', $moduleName, 2);
        }

        $isResource     = 0 === strpos($path, 'Resources') && null !== $dir;
        $overridePath   = substr($path, 9);
        $resourceModule = null;
        $modules        = array($this->getModule($moduleName));
        $files          = array();

        foreach ($modules as $module) {
            if ($isResource && file_exists($file = $dir . '/' . $module->getName() . $overridePath)) {
                if (null !== $resourceModule) {
                    throw new \RuntimeException(sprintf('"%s" resource is hidden by a resource from the "%s" derived module. Create a "%s" file to override the module resource.',
                        $file,
                        $resourceModule,
                        $dir . '/' . $modules[0]->getName() . $overridePath
                    ));
                }

                if ($first) {
                    return $file;
                }
                $files[] = $file;
            }

            if (file_exists($file = $this->getResourcesPath($module) . '/' . $path)) {
                if ($first && !$isResource) {
                    return $file;
                }
                $files[]        = $file;
                $resourceModule = $module->getName();
            }
        }

        if (count($files) > 0) {
            return $first && $isResource ? $files[0] : $files;
        }

        throw new \InvalidArgumentException(sprintf('Unable to find file "%s".', $name));
    }

    protected function getResourcesPath($module)
    {
        if (is_callable(array($module, 'getResourcesPath'))) {
            $resourcesPath  = $module->getResourcesPath();
        } else {
            $resourcesPath = $module->getPath();
        }

        return $resourcesPath;
    }
}
