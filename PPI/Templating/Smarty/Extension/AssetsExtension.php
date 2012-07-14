<?php
/**
 * This file is part of the PPI Framework.
 *
 * @category    PPI
 * @package     Templating
 * @copyright   Copyright (c) 2012 Paul Dragoonis <dragoonis@php.net>
 * @license     http://opensource.org/licenses/mit-license.php MIT
 * @link        http://www.ppi.io
 */

 namespace PPI\Templating\Smarty\Extension;

 use NoiseLabs\Bundle\SmartyBundle\Extension\AssetsExtension as BaseAssetsExtension;
 use Symfony\Component\Templating\Helper\AssetsHelper;

 /**
  * Provides helper functions to link to assets (images, Javascript,
  * stylesheets, etc.).
  *
  * @author Vítor Brandão <noisebleed@noiselabs.org>
  */
 class AssetsExtension extends BaseAssetsExtension
 {
    protected $assetsHelper = null;

    public function __construct(AssetsHelper $assetsHelper)
    {
        $this->assetsHelper = $assetsHelper;
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
     * Returns the public path of an asset
     *
     * Absolute paths (i.e. http://...) are returned unmodified.
     *
     * @param string $path A public path
     *
     * @return string A public path which takes into account the base path and URL path
     */
    public function getAssetUrl_block(array $parameters = array(), $path = null, $template, &$repeat)
    {
        // only output on the closing tag
        if (!$repeat) {
            $parameters = array_merge(array(
                'package'   => null,
            ), $parameters);

            return $this->assetsHelper->getUrl($path, $parameters['package']);
        }
    }

    /**
     * Returns the public path of an asset
     *
     * Absolute paths (i.e. http://...) are returned unmodified.
     *
     * @param string $path A public path
     *
     * @return string A public path which takes into account the base path
     * and URL path
     */
    public function getAssetUrl_modifier($path, $package = null)
    {
        return $this->assetsHelper->getUrl($path, $package);
    }

    /**
     * Returns the version of the assets in a package
     *
     * @return int
     */
    public function getAssetsVersion(array $parameters = array(), \Smarty_Internal_Template $template)
    {
        $parameters = array_merge(array(
                'package'   => null,
        ), $parameters);

        return $this->assetsHelper->getVersion($parameters['package']);
    }

 }
