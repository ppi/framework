<?php

namespace PPI\Templating;

use Symfony\Component\Config\FileLocator as BaseFileLocator;

/**
 * FileLocator uses the KernelInterface to locate resources in bundles.
 *
 */
class FileLocator extends BaseFileLocator
{
    private $modules;
    private $path;
	private $baseModulePath;

    /**
     * Constructor.
     *
     * @param KernelInterface $kernel A KernelInterface instance
     * @param string          $path   The path the global resource directory
     * @param string|array    $paths A path or an array of paths where to look for resources
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

			$moduleName = substr($file, 1);
			if (false !== strpos($moduleName, '/')) {
				$path = '';
			   list($moduleName, $templatePath) = explode('/', $moduleName, 2);
		   }

			foreach($this->modules as $module) {
				$modulePath = $this->baseModulePath.'/'.$module.'/';
				$path = $modulePath . $templatePath;
				if(file_exists($path)) {
					if ($first) {
						return $path;
					}
					$files[] = $path;
				}
			}
			
			
			
        } else {
			
			$path = $this->appPath . '/' . $file;
			if(file_exists($path)) {
				return $path;
			}
			
			throw new \InvalidArgumentException(sprintf('Unable to find file "%s".', $file));
		}

//        return parent::locate($file, $currentPath, $first);
    }
}