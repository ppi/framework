<?php
/**
 * This file is part of the PPI Framework.
 *
 * @copyright   Copyright (c) 2012 Paul Dragoonis <paul@ppi.io>
 * @license     http://opensource.org/licenses/mit-license.php MIT
 * @link        http://www.ppi.io
 */
namespace PPI\ServiceManager\Config;

use PPI\Templating\DelegatingEngine,
    PPI\Templating\FileLocator,
    PPI\Templating\GlobalVariables,
    PPI\Templating\TemplateLocator,
    PPI\Templating\TemplateNameParser,
    PPI\Templating\Helper\RouterHelper,
    PPI\Templating\Helper\SessionHelper,
    PPI\Templating\Php\FileSystemLoader,
    PPI\Templating\Twig\TwigEngine,
    PPI\Templating\Twig\Loader\FileSystemLoader as TwigFileSystemLoader,
    PPI\Templating\Twig\Extension\AssetsExtension as TwigAssetsExtension,
    PPI\Templating\Twig\Extension\RouterExtension as TwigRouterExtension,
    PPI\Templating\Smarty\SmartyEngine,
    PPI\Templating\Smarty\Extension\AssetsExtension as SmartyAssetsExtension,
    PPI\Templating\Smarty\Extension\RouterExtension as SmartyRouterExtension,
    Symfony\Component\Templating\PhpEngine,
    Symfony\Component\Templating\Helper\SlotsHelper,
    Symfony\Component\Templating\Helper\AssetsHelper,
    Zend\ServiceManager\Config,
    Zend\ServiceManager\ServiceManager;

/**
 * ServiceManager configuration for the Templating component.
 *
 * @author     Vítor Brandão <vitor@ppi.io>
 * @package    PPI
 * @subpackage ServiceManager
 */
class TemplatingConfig extends Config
{
    /**
     * Templating engines currently supported:
     * * PHP
     * * Twig
     * * Smarty
     *
     * @param ServiceManager $serviceManager
     *
     * @return type
     */
    public function configureServiceManager(ServiceManager $serviceManager)
    {
        // these are the templating engines currently supported
        $knownEngineIds = array('php', 'smarty', 'twig');

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
                new TemplateNameParser(),
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

            $smartyEngine = new SmartyEngine(
                new \Smarty(),
                $serviceManager->get('templating.locator'),
                new TemplateNameParser(),
                new FileSystemLoader($serviceManager->get('templating.locator')),
                array(
                    'cache_dir'     => $cacheDir.DIRECTORY_SEPARATOR.'cache',
                    'compile_dir'   => $cacheDir.DIRECTORY_SEPARATOR.'templates_c',
                ),
                $serviceManager->get('templating.globals')
            );

            // Add some SmartyBundle extensions
            $smartyEngine->addExtension(new SmartyAssetsExtension($serviceManager->get('templating.helper.assets')));
            $smartyEngine->addExtension(new SmartyRouterExtension($serviceManager->get('router')));

            return $smartyEngine;
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
