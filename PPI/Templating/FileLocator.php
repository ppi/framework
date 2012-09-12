<?php
/**
 * This file is part of the PPI Framework.
 *
 * @category    PPI
 * @package     Templating
 * @copyright   Copyright (c) 2012 Paul Dragoonis <paul@ppi.io>
 * @license     http://opensource.org/licenses/mit-license.php MIT
 * @link        http://www.ppi.io
 */


namespace PPI\Templating;

use Symfony\Component\Config\FileLocator as BaseFileLocator;

/**
 * FileLocator is used to locate template resources
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Paul Dragoonis <paul@ppi.io>
 * @author Vítor Brandão <vitor@ppi.io>
 */
class FileLocator extends BaseFileLocator
{
    private $modules;
    private $path;
    private $baseModulePath;

    /**
     * Constructor.
     *
     * @param array        $options
     * @param string       $path    The path the global resource directory
     * @param string|array $paths   A path or an array of paths where to look for resources
     */
    public function __construct(array $options = array(), $path = null, array $paths = array())
    {
        $this->modules        = $options['modules'];
        $this->baseModulePath = $options['modulesPath'];
        $this->appPath        = $options['appPath'];
        $this->path           = $path;
        $paths[]              = $path;

        parent::__construct($paths);
    }

    public function locate($file, $currentPath = null, $first = true)
    {
        
        if ('@' === $file[0]) {
            
           if (false !== strpos($file, '..')) {
               throw new \RuntimeException(sprintf('File name "%s" contains invalid characters (..).', $file));
           }

            $path = $this->baseModulePath . '/' . substr($file, 1);
            if (file_exists($path)) {
                if ($first) {
                    return $path;
                }
                $files[] = $path;
            }

        } else {

            $path = $this->appPath . '/' . $file;
            if (file_exists($path)) {
                return $path;
            }

            throw new \InvalidArgumentException(sprintf('Unable to find file "%s".', $file));
        }
        
    }

    /**
     * Returns the path to the app directory.
     *
     * @return string The path to the app directory.
     */
    public function getAppPath()
    {
        return $this->appPath;
    }

    /**
     * Returns an array of paths to modules.
     *
     * @return array An array of paths to each loaded module
     */
    public function getModulesPath()
    {
        $paths = array();
        foreach ($this->modules as $module) {
            $paths[$module] = $this->baseModulePath.DIRECTORY_SEPARATOR.$module;
        }

        return $paths;
    }
}
