<?php

/**
 * The PPI Template Locator
 *
 * @package   Core
 * @author    Paul Dragoonis <dragoonis@php.net>
 * @license   http://opensource.org/licenses/mit-license.php MIT
 * @link      http://www.ppi.io
 */
namespace PPI\Templating;

use PPI\Templating\TemplateReference;
use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\Templating\TemplateReferenceInterface;

/**
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Vítor Brandão <noisebleed@noiselabs.org>
 */
class TemplateLocator implements FileLocatorInterface
{
    protected $locator;
    protected $cache;

    /**
     * Constructor.
     *
     * @param FileLocatorInterface $locator  A FileLocatorInterface instance
     * @param string               $cacheDir The cache path
     */
    public function __construct(FileLocatorInterface $locator, $cacheDir = null)
    {
        if (null !== $cacheDir && is_file($cache = $cacheDir.'/templates.php')) {
            $this->cache = require $cache;
        }

        $this->locator = $locator;
    }

    /**
     * Returns a full path for a given file.
     *
     * @param TemplateReferenceInterface $template A template
     *
     * @return string The full path for the file
     */
    protected function getCacheKey($template)
    {
        return $template->getLogicalName();
    }

    /**
     * Returns a full path for a given file.
     *
     * @param TemplateReferenceInterface $template    A template
     * @param string                     $currentPath Unused
     * @param Boolean                    $first       Unused
     *
     * @return string The full path for the file
     *
     * @throws \InvalidArgumentException When the template is not an instance of TemplateReferenceInterface
     * @throws \InvalidArgumentException When the template file can not be found
     */
    public function locate($template, $currentPath = null, $first = true)
    {

        if (!$template instanceof TemplateReferenceInterface) {
            throw new \InvalidArgumentException("The template must be an instance of TemplateReferenceInterface.");
        }

        $key = $this->getCacheKey($template);

        if (isset($this->cache[$key])) {
            return $this->cache[$key];
        }

        try {
            return $this->cache[$key] = $this->locator->locate($template->getPath(), $currentPath);
        } catch (\InvalidArgumentException $e) {
            throw new \InvalidArgumentException(sprintf('Unable to find template "%s" : "%s".', $template, $e->getMessage()), 0, $e);
        }
    }

    /**
     * Returns the path to the views directory in the app dir.
     *
     * @return string The path to the app directory.
     */
    public function getAppPath()
    {
        return $this->locator->appPath.DIRECTORY_SEPARATOR.TemplateReference::APP_VIEWS_DIRECTORY;;
    }

    /**
     * Returns an array of paths to modules views dir.
     *
     * @return array An array of paths to each loaded module
     */
    public function getModulesPath()
    {
        $paths = $this->locator->getModulesPath();
        foreach (array_keys($paths) as $module) {
            $paths[$module] .= DIRECTORY_SEPARATOR.TemplateReference::MODULE_VIEWS_DIRECTORY;
        }

        return $paths;
    }
}
