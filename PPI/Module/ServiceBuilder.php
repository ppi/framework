<?php
/**
 * This file is part of the PPI Framework.
 *
 * @category    PPI
 * @package     Core
 * @copyright   Copyright (c) 2012 Paul Dragoonis <paul@ppi.io>
 * @license     http://opensource.org/licenses/mit-license.php MIT
 * @link        http://www.ppi.io
 */

namespace PPI\Module;

use PPI\Templating\TemplateNameParser;
use PPI\Templating\Helper\RouterHelper;
use PPI\Templating\Helper\SessionHelper;
use PPI\Templating\Php\FileSystemLoader;
use Symfony\Component\Templating\PhpEngine;
use Symfony\Component\Templating\Helper\SlotsHelper;

/**
 * This class is able to create services required in PPI App.
 *
 * @author Vítor Brandão <vitor@ppi.io>
 */
class ServiceBuilder
{
    protected $services;

    /**
     * @param PPI\Module\ServiceLocator $services
     */
    public function __construct(ServiceLocator $services)
    {
        $this->services = $services;
    }
  
    protected function createTemplatingEnginePhpService()
    {
        $engine = new PhpEngine(
            new TemplateNameParser(),
            new FileSystemLoader($this->get('templating.locator')),
            array(
                new SlotsHelper(),
                $this->get('templating.assets.helper'),
                new RouterHelper($this->get('router')),
                new SessionHelper($this->get('session'))
            )
        );

        return $engine;
    }
    
    protected function createTemplatingEngineSmartyService()
    {
        if (null == $cacheDir = $this->getAppConfigValue('cache_dir')) {
                    $cacheDir = $fileLocator->getAppPath().DIRECTORY_SEPARATOR.'cache';
                }
                $cacheDir .= DIRECTORY_SEPARATOR.'smarty';

                $smartyEngine = new SmartyEngine(
                    new \Smarty(),
                    $templateLocator,
                    new TemplateNameParser(),
                    new FileSystemLoader($templateLocator),
                    array(
                        'cache_dir'     => $cacheDir.DIRECTORY_SEPARATOR.'cache',
                        'compile_dir'   => $cacheDir.DIRECTORY_SEPARATOR.'templates_c',
                    )
                );

                // Add some SmartyBundle extensions
                $smartyEngine->addExtension(new \PPI\Templating\Smarty\Extension\AssetsExtension($assetsHelper));
                $smartyEngine->addExtension(new \PPI\Templating\Smarty\Extension\RouterExtension($this->_router));

                return $smartyEngine;        
    }

    /**
     * @return PPI\Templating\Twig\TwigEngine
     */
    protected function createTemplatingEngineTwigService()
    {
        $twigEnvironment = new \Twig_Environment(
            new TwigFileSystemLoader(
                $templateLocator,
                new TemplateNameParser()
            )
        );

        // Add some twig extension
        $twigEnvironment->addExtension(new \PPI\Templating\Twig\Extension\AssetsExtension($assetsHelper));
        $twigEnvironment->addExtension(new \PPI\Templating\Twig\Extension\RouterExtension($this->_router));

        // Return the twig engine
        return new TwigEngine(
            $twigEnvironment,
            new TemplateNameParser(),
            $templateLocator
        );
    }

    /**
     * Gets a service.
     */
    protected function get($id)
    {
        return $this->services->get($id);
    }
}
