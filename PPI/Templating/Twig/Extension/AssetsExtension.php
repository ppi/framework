<?php

/**
 * The PPI Twig AssetsExtension
 *
 * @package   Core
 * @author    Paul Dragoonis <dragoonis@php.net>
 * @license   http://opensource.org/licenses/mit-license.php MIT
 * @link      http://www.ppi.io
 */

namespace PPI\Templating\Twig\Extension;

use Symfony\Component\Templating\Helper\AssetsHelper;

class AssetsExtension extends \Twig_Extension
{
    protected $assetsHelper = null;

    public function __construct(AssetsHelper $assetsHelper)
    {
        $this->assetsHelper = $assetsHelper;
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
    */
    public function getFunctions()
    {
        return array(
            'asset' => new \Twig_Function_Method($this, 'getAssetUrl'),
            'assets_version' => new \Twig_Function_Method($this, 'getAssetsVersion'),
        );
    }

    /**
     * Returns the public path of an asset.
     *
     * Absolute paths (i.e. http://...) are returned unmodified.
     *
     * @param string $path        A public path
     * @param string $packageName The name of the asset package to use
     *
     * @return string A public path which takes into account the base path and URL path
    */
    public function getAssetUrl($path, $packageName = null)
    {
        return $this->assetsHelper->getUrl($path, $packageName);
    }

    /**
     * Returns the version of the assets in a package.
     *
     * @param string $packageName
     *
     * @return int
    */
    public function getAssetsVersion($packageName = null)
    {
        return $this->assetsHelper->getVersion($packageName);
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
    */
    public function getName()
    {
        return 'assets';
    }

}
