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

use PPI\Templating\FileLocator;
use PPI\Templating\GlobalVariables;
use PPI\Templating\TemplateLocator;
use PPI\Templating\DelegatingEngine;
use PPI\Templating\TemplateNameParser;
use PPI\Templating\Php\FileSystemLoader;
use Symfony\Component\Templating\PhpEngine;

// Helpers
use PPI\Templating\Helper\RouterHelper;
use PPI\Templating\Helper\SessionHelper;
use Symfony\Component\Templating\Helper\SlotsHelper;
use Symfony\Component\Templating\Helper\AssetsHelper;

// Twig
use PPI\Templating\Twig\TwigEngine;
use PPI\Templating\Twig\Loader\FileSystemLoader as TwigFileSystemLoader;
use PPI\Templating\Twig\Extension\AssetsExtension as TwigAssetsExtension;
use PPI\Templating\Twig\Extension\RouterExtension as TwigRouterExtension;

// Mustache
use PPI\Templating\Mustache\MustacheEngine;
use PPI\Templating\Mustache\Loader\FilesystemLoader as MustacheFileSystemLoader;

// Smarty
use PPI\Templating\Smarty\SmartyEngine;
use PPI\Templating\Smarty\Extension\AssetsExtension as SmartyAssetsExtension;
use PPI\Templating\Smarty\Extension\RouterExtension as SmartyRouterExtension;

// Service Manager
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
        $knownEngineIds = array('php', 'smarty', 'twig', 'mustache');

        // these are the engines selected by the user
        $engineIds = $serviceManager->getOption( 'templating.engines');

        // filter templating engines
        $engineIds = array_intersect($engineIds, $knownEngineIds);
        if (empty($engineIds)) {
            throw new \RuntimeException(sprintf('At least one templating engine should be defined in your app config (in $config[\'templating.engines\']). These are the available ones: "%s". Example: "$config[\'templating.engines\'] = array(\'%s\');"', implode('", ', $knownEngineIds), implode("', ", $knownEngineIds)));
        }

        // File locator
        $serviceManager->setFactory('filelocator', function($serviceManager) {
            return new FileLocator(array(
                'modules'     => $serviceManager->get('module.manager')->getModules(),
                'modulesPath' => realpath($serviceManager['moduleConfig']['listenerOptions']['module_paths'][0]),
                'appPath'     => $serviceManager->getOption('app.root_dir')
            ));
        });

        // Templating locator
        $serviceManager->setFactory('templating.locator', function($serviceManager) {
            return new TemplateLocator($serviceManager->get('filelocator'));
        });
        
        // Templating Name Parser
        $serviceManager->setFactory('templating.name.parser', function($serviceManager) {
            return new TemplateNameParser();
        });

        // Templating assets helper
        $serviceManager->setFactory('templating.helper.assets', function($serviceManager) {
            return new AssetsHelper($serviceManager->get('request')->getBasePath());
        });

        // Templating globals
        $serviceManager->setFactory('templating.globals', function($serviceManager) {
            return new GlobalVariables($serviceManager->get('servicemanager'));
        });

        // PHP Engine
        $serviceManager->setFactory('templating.engine.php', function($serviceManager) {
            return new PhpEngine(
                $serviceManager->get('templating.name.parser'),
                new FileSystemLoader($serviceManager->get('templating.locator')),
                array(
                    new SlotsHelper(),
                    $serviceManager->get('templating.helper.assets'),
                    new RouterHelper($serviceManager->get('router')),
                    new SessionHelper($serviceManager->get('session'))
                 )
            );
        });
        
        // Twig Engine
        $serviceManager->setFactory('templating.engine.twig', function($serviceManager) {

            $templatingLocator = $serviceManager->get('templating.locator');

            $twigEnvironment = new \Twig_Environment(
                new TwigFileSystemLoader($templatingLocator, new TemplateNameParser())
            );

            // Add some twig extension
            $twigEnvironment->addExtension(new TwigAssetsExtension($serviceManager->get('templating.helper.assets')));
            $twigEnvironment->addExtension(new TwigRouterExtension($serviceManager->get('router')));

            return new TwigEngine($twigEnvironment, new TemplateNameParser(), $templatingLocator, $serviceManager->get('templating.globals'));
        });

        // Smarty Engine
        $serviceManager->setFactory('templating.engine.smarty', function($serviceManager) {
            $cacheDir = $serviceManager->getOption('app.cache_dir').DIRECTORY_SEPARATOR.'smarty';
            $templateLocator = $serviceManager->get('templating.locator');
            
            $smartyEngine = new SmartyEngine(
                new \Smarty(),
                $templateLocator,
                new TemplateNameParser(),
                new FileSystemLoader($templateLocator),
                array(
                    'cache_dir'     => $cacheDir . DIRECTORY_SEPARATOR . 'cache',
                    'compile_dir'   => $cacheDir . DIRECTORY_SEPARATOR . 'templates_c',
                ),
                $serviceManager->get('templating.globals')
            );

            // Add some SmartyBundle extensions
            $smartyEngine->addExtension(new SmartyAssetsExtension($serviceManager->get('templating.helper.assets')));
            $smartyEngine->addExtension(new SmartyRouterExtension($serviceManager->get('router')));

            return $smartyEngine;
        });
        
        // Mustache Engine
        $serviceManager->setFactory('templating.engine.mustache', function($serviceManager) {

            $rawMustacheEngine = new \Mustache_Engine(array(
                'loader' => new MustacheFileSystemLoader($serviceManager->get('templating.locator'), new TemplateNameParser()),
                'cache'  => $serviceManager->getOption('app.cache_dir') . DIRECTORY_SEPARATOR . 'mustache'
            ));

            return new MustacheEngine($rawMustacheEngine, new TemplateNameParser());
        });

        // Delegating Engine
        $serviceManager->setFactory('templating', function($serviceManager) use ($engineIds) {
            $delegatingEngine = new DelegatingEngine();
            foreach ($engineIds as $id) {
                $delegatingEngine->addEngine($serviceManager->get('templating.engine.'.$id));
            }

            return $delegatingEngine;
        });
    }
}
