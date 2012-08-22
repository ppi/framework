<?php
/**
 * This file is part of the PPI Framework.
 *
 * @category    PPI
 * @package     ServiceManager
 * @copyright   Copyright (c) 2012 Paul Dragoonis <paul@ppi.io>
 * @license     http://opensource.org/licenses/mit-license.php MIT
 * @link        http://www.ppi.io
 */

namespace PPI\ServiceManager\Config;

use PPI\Templating\DelegatingEngine;
use PPI\Templating\FileLocator;
use PPI\Templating\TemplateLocator;
use PPI\Templating\TemplateNameParser;
use PPI\Templating\Helper\RouterHelper;
use PPI\Templating\Helper\SessionHelper;
use PPI\Templating\Php\FileSystemLoader;
use PPI\Templating\Twig\TwigEngine;
use PPI\Templating\Twig\Loader\FileSystemLoader as TwigFileSystemLoader;
use PPI\Templating\Twig\Extension\AssetsExtension as TwigAssetsExtension;
use PPI\Templating\Twig\Extension\RouterExtension as TwigRouterExtension;
use PPI\Templating\Smarty\SmartyEngine;
use PPI\Templating\Smarty\Extension\AssetsExtension as SmartyAssetsExtension;
use PPI\Templating\Smarty\Extension\RouterExtension as SmartyRouterExtension;
use Symfony\Component\Templating\PhpEngine;
use Symfony\Component\Templating\Helper\SlotsHelper;
use Symfony\Component\Templating\Helper\AssetsHelper;
use Zend\ServiceManager\Config;
use Zend\ServiceManager\ServiceManager;

/**
 * ServiceManager configuration for the Templating component.
 *
 * @author Vítor Brandão <vitor@ppi.io>
 */
class TemplatingConfig extends Config
{
    /**
     * Templating engines currently supported:
     * * PHP
     * * Twig
     * * Smarty
     */
    public function configureServiceManager(ServiceManager $serviceManager)
    {
        // these are the templating engines currently supported
        $knownEngineIds = array('php', 'smarty', 'twig');

        // these are the engines selected by the user
        $options =  $serviceManager->get('options');
        $engineIds = isset($options['config']['templating.engines']) ?
            $options['config']['templating.engines'] : $options['templating.engines'];

        // filter templating engines
        $engineIds = array_intersect($engineIds, $knownEngineIds);
        if (empty($engineIds)) {
            throw new \RuntimeException(sprintf('At least one templating engine should be defined in your app config (in $config[\'templating.engines\']). These are the available ones: "%s". Example: "$config[\'templating.engines\'] = array(\'%s\');"', implode('", ', $knownEngineIds), implode("', ", $knownEngineIds)));
        }

        // File locator
        $serviceManager->setFactory('filelocator', function($serviceManager) use ($options) {
            return new FileLocator(array(
                'modules'     => $serviceManager->get('module.manager')->getModules(),
                'modulesPath' => realpath($options['moduleConfig']['listenerOptions']['module_paths'][0]),
                'appPath'     => getcwd() . '/app'
            ));
        });

        // Templating locator
        $serviceManager->setFactory('templating.locator', function($serviceManager) {
            new TemplateLocator($serviceManager->get('filelocator'));
        });

        // Templating assets helper
        $serviceManager->setFactory('templating.helper.assets', function($serviceManager) {
            return new AssetsHelper($serviceManager->get('request')->getBasePath());
        });

        // PHP Engine
        $serviceManager->setFactory('templating.engine.php', function($serviceManager) {
            return new PhpEngine(
                new TemplateNameParser(),
                new FileSystemLoader($serviceManager->get('templating.locator')),
                array(
                    new SlotsHelper(),
                    $serviceManager->get('templating.assets.helper'),
                    new RouterHelper($serviceManager->get('router')),
                    new SessionHelper($serviceManager->get('session'))
                )
            );
        });

        // Twig Engine
        $serviceManager->setFactory('templating.engine.twig', function($serviceManager) {
            $twigEnvironment = new \Twig_Environment(
                new TwigFileSystemLoader($templateLocator, new TemplateNameParser())
            );

            // Add some twig extension
            $twigEnvironment->addExtension(new TwigAssetsExtension($serviceManager->get('templating.assets.helper')));
            $twigEnvironment->addExtension(new TwigRouterExtension($serviceManager->get('router')));

            return new TwigEngine($twigEnvironment, new TemplateNameParser(), $templateLocator);
        });

        // Smarty Engine
        $serviceManager->setFactory('templating.engine.smarty', function($serviceManager) use ($options) {
            if (!isset($options['config']['cache_dir']) || (null == $cacheDir = $options['config']['cache_dir'])) {
                $fileLocator = $serviceManager->get('filelocator');
                $cacheDir = $fileLocator->getAppPath().DIRECTORY_SEPARATOR.'cache';
            }
            $cacheDir .= DIRECTORY_SEPARATOR.'smarty';

            $smartyEngine = new SmartyEngine(
                new \Smarty(),
                $serviceManager->get('templating.locator'),
                new TemplateNameParser(),
                new FileSystemLoader($serviceManager->get('templating.locator')),
                array(
                    'cache_dir'     => $cacheDir.DIRECTORY_SEPARATOR.'cache',
                    'compile_dir'   => $cacheDir.DIRECTORY_SEPARATOR.'templates_c',
                )
            );

            // Add some SmartyBundle extensions
            $smartyEngine->addExtension(new SmartyAssetsExtension($serviceManager->get('templating.assets.helper')));
            $smartyEngine->addExtension(new SmartyRouterExtension($serviceManager->get('router')));

            return $smartyEngine;
        });

        // Delegating Engine
        $serviceManager->setFactory('templating', function($serviceManager) use ($engineIds) {
            $engines = array();
            foreach ($engineIds as $id) {
                $engines[] = $serviceManager->get('templating.engine.'.$id);
            }

            return new DelegatingEngine($engines);
        });
    }
}
