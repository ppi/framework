<?php
/**
 * This file is part of the PPI Framework.
 *
 * @copyright  Copyright (c) 2012 Paul Dragoonis <paul@ppi.io>
 * @license    http://opensource.org/licenses/mit-license.php MIT
 * @link       http://www.ppi.io
 */
namespace PPI\Templating\Smarty\Extension;

use NoiseLabs\Bundle\SmartyBundle\Extension\AssetsExtension as BaseAssetsExtension,
    Symfony\Component\Templating\Helper\AssetsHelper;

/**
 * Provides helper functions to link to assets (images, Javascript,
 * stylesheets, etc.).
 *
 * @author     Vítor Brandão <vitor@noiselabs.org>
 * @package    PPI
 * @subpackage Templating
 */
class AssetsExtension extends BaseAssetsExtension
{
    /**
     * @todo Add inline documentation.
     *
     * @var AssetsHelper
     */
    protected $assetsHelper = null;

    /**
     * A key/value pair of functions to remap to help comply with PSR standards
     * 
     * @var array
     */
    protected $funRemap = array(
        'getAssetUrl_block' => 'getAssetUrlBlock',
        'getAssetUrl_modifier' => 'getAssetUrlModifier',
    );

    /**
     * Constructor.
     *
     * @param AssetsHelper $assetsHelper
     *
     * @return void
     */
    public function __construct(AssetsHelper $assetsHelper)
    {
        $this->assetsHelper = $assetsHelper;
    }

    /**
     * The magic call method triggers before throwing an exception
     *
     * @param  string $method The method you are looking for
     * @param  array  $params The params you wish to pass to your method
     * 
     * @return mixed
     */
    public function __call($method, array $params = array()) {
        if(isset($this->funRemap[$method])) {
            return call_user_func_array(array($this, $this->funRemap[$method]), $params);
        }
        throw new \BadMethodCallException('Method ' . $method . ' does not exist');
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
     * Returns the public path of an asset. Absolute paths (i.e. http://...) are
     * returned unmodified.
     *
     * @param array $parameters
     * @param type  $path
     * @param type  $template
     * @param type  $repeat
     *
     * @return string A public path which takes into account the base path and URL path
     */
    public function getAssetUrlBlock(array $parameters = array(), $path = null, $template, &$repeat)
    {
        // only output on the closing tag
        if (!$repeat) {
            $parameters = array_merge(array(
                'package' => null,
            ), $parameters);

            return $this->assetsHelper->getUrl($path, $parameters['package']);
        }
    }

    /**
     * Returns the public path of an asset
     *
     * Absolute paths (i.e. http://...) are returned unmodified.
     *
     * @param string $path    A public path
     * @param type   $package
     *
     * @return string A public path which takes into account the base path
     *                and URL path
     */
    public function getAssetUrlModifier($path, $package = null)
    {
        return $this->assetsHelper->getUrl($path, $package);
    }

    /**
     * Returns the version of the assets in a package.
     *
     * @param array                     $parameters
     * @param \Smarty_Internal_Template $template
     *
     * @return integer
     */
    public function getAssetsVersion(array $parameters = array(), \Smarty_Internal_Template $template)
    {
        $parameters = array_merge(array(
            'package' => null,
        ), $parameters);

        return $this->assetsHelper->getVersion($parameters['package']);
    }

}
