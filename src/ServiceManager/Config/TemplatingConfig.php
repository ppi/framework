<?php
/**
 * This file is part of the PPI Framework.
 *
 * @copyright   Copyright (c) 2011-2013 Paul Dragoonis <paul@ppi.io>
 * @license     http://opensource.org/licenses/mit-license.php MIT
 * @link        http://www.ppi.io
 */

namespace PPI\ServiceManager\Config;

use PPI\View\GlobalVariables;
use PPI\View\TemplateLocator;
use PPI\View\DelegatingEngine;
use PPI\View\TemplateNameParser;

use Symfony\Bundle\FrameworkBundle\Templating\Loader\FilesystemLoader;
use Symfony\Component\Templating\PhpEngine;

// Helpers
use PPI\View\Helper\RouterHelper;
use PPI\View\Helper\SessionHelper;
use Symfony\Component\Templating\Helper\SlotsHelper;
use Symfony\Component\Templating\Helper\AssetsHelper;

// Mustache
use PPI\View\Mustache\MustacheEngine;
use PPI\View\Mustache\Loader\FileSystemLoader as MustacheFileSystemLoader;

// Twig

// Smarty
use PPI\View\Smarty\Extension\AssetsExtension as SmartyAssetsExtension;
use PPI\View\Smarty\Extension\RouterExtension as SmartyRouterExtension;

// Service Manager
use Zend\ServiceManager\ServiceManager;

/**
 * ServiceManager configuration for the Templating component.
 *
 * @author     Vítor Brandão <vitor@ppi.io>
 * @package    PPI
 * @subpackage ServiceManager
 */
class TemplatingConfig extends AbstractConfig
{
    /**
     * Templating engines currently supported:
     * - PHP
     * - Twig
     * - Smarty
     * - Mustache
     *
     * @param  ServiceManager    $serviceManager
     * @throws \RuntimeException
     */
    public function configureServiceManager(ServiceManager $serviceManager)
    {
        $config = $serviceManager->get('Config');
        $appRootDir = $config['parameters']['app.root_dir'];
        $appCacheDir = $config['parameters']['app.cache_dir'];
        $appCharset = $config['parameters']['app.charset'];

        // The "framework.templating" option is deprecated. Please replace it with "framework.view"
        $config = $this->processConfiguration($config);

        // these are the templating engines currently supported
        $knownEngineIds = array('php', 'smarty', 'twig', 'mustache');

        // these are the engines selected by the user
        $engineIds = isset($config['engines']) ? $config['engines'] : array('php');

        // filter templating engines
        $engineIds = array_intersect($engineIds, $knownEngineIds);
        if (empty($engineIds)) {
            throw new \RuntimeException(sprintf('At least one templating engine should be defined in your app config (in $config[\'view.engines\']). These are the available ones: "%s". Example: "$config[\'templating.engines\'] = array(\'%s\');"', implode('", ', $knownEngineIds), implode("', ", $knownEngineIds)));
        }

        /*
         * Templating Locator.
         */
        $serviceManager->setFactory('templating.locator', function ($serviceManager) use ($appCacheDir) {
            return new TemplateLocator(
                $serviceManager->get('file_locator'),
                $appCacheDir
            );
        });

        /*
         * Templating Name Parser.
         */
        $serviceManager->setFactory('templating.name_parser', function ($serviceManager) {
            return new TemplateNameParser($serviceManager->get('modulemanager'));
        });

        /*
         * Filesystem Loader.
         */
        $serviceManager->setFactory('templating.loader.filesystem', function ($serviceManager) {
            return new FileSystemLoader($serviceManager->get('templating.locator'));
        });

        /*
         * Templating assets helper.
         */
        $serviceManager->setFactory('templating.helper.assets', function ($serviceManager) {
            return new AssetsHelper($serviceManager->get('request')->getBasePath());
        });

        /*
         * Templating globals.
         */
        $serviceManager->setFactory('templating.globals', function ($serviceManager) {
            return new GlobalVariables($serviceManager->get('servicemanager'));
        });

        /*
         * PHP Engine.
         *
         * TODO: Migrate to Symfony\Bundle\FrameworkBundle\Templating\PhpEngine
         */
        $serviceManager->setFactory('templating.engine.php', function ($serviceManager) use ($appCharset) {
            $engine = new PhpEngine(
                $serviceManager->get('templating.name_parser'),
                $serviceManager->get('templating.loader'),
                array(
                    new SlotsHelper(),
                    $serviceManager->get('templating.helper.assets'),
                    new RouterHelper($serviceManager->get('router')),
                    new SessionHelper($serviceManager->get('session'))
                 )
            );

            $engine->addGlobal('app', $serviceManager->get('templating.globals'));
            $engine->setCharset($appCharset);

            return $engine;
        });

        /*
         * Twig Engine
         */
        $serviceManager->setFactory('templating.engine.twig', function ($serviceManager) {
            $twigEnvironment = new \Twig_Environment(
                new \PPI\View\Twig\Loader\FileSystemLoader(
                    $serviceManager->get('templating.locator'),
                    $serviceManager->get('templating.name_parser'))
            );

            // Add some twig extension
            $twigEnvironment->addExtension(new \PPI\View\Twig\Extension\AssetsExtension($serviceManager->get('templating.helper.assets')));
            $twigEnvironment->addExtension(new \PPI\View\Twig\Extension\RouterExtension($serviceManager->get('router')));

            return new \PPI\View\Twig\TwigEngine($twigEnvironment, $serviceManager->get('templating.name_parser'),
                $serviceManager->get('templating.locator'), $serviceManager->get('templating.globals'));
        });

        /*
         * Smarty Engine.
         */
        $serviceManager->setFactory('templating.engine.smarty', function ($serviceManager) use ($appCacheDir) {
            $cacheDir = $appCacheDir . DIRECTORY_SEPARATOR . 'smarty';

            $smartyEngine = new \PPI\View\Smarty\SmartyEngine(
                new \Smarty(),
                $serviceManager->get('templating.locator'),
                $serviceManager->get('templating.name_parser'),
                $serviceManager->get('templating.loader'),
                array(
                    'cache_dir'     => $cacheDir . DIRECTORY_SEPARATOR . 'cache',
                    'compile_dir'   => $cacheDir . DIRECTORY_SEPARATOR . 'templates_c',
                ),
                $serviceManager->get('templating.globals'),
                $serviceManager->get('logger')
            );

            // Add some SmartyBundle extensions
            $smartyEngine->addExtension(new SmartyAssetsExtension($serviceManager->get('templating.helper.assets')));
            $smartyEngine->addExtension(new SmartyRouterExtension($serviceManager->get('router')));

            return $smartyEngine;
        });

        // Mustache Engine
        $serviceManager->setFactory('templating.engine.mustache', function ($serviceManager, $appCacheDir) {

            $rawMustacheEngine = new \Mustache_Engine(array(
                'loader' => new MustacheFileSystemLoader($serviceManager->get('templating.locator'),
                    $serviceManager->get('templating.name_parser')),
                'cache'  => $appCacheDir . DIRECTORY_SEPARATOR . 'mustache'
            ));

            return new MustacheEngine($rawMustacheEngine, $serviceManager->get('templating.name_parser'));
        });

        /*
         * Delegating Engine.
         */
        $serviceManager->setFactory('templating.engine.delegating', function ($serviceManager) use ($engineIds) {
            $delegatingEngine = new DelegatingEngine();
            // @todo - lazy load this
            foreach ($engineIds as $id) {
                $delegatingEngine->addEngine($serviceManager->get('templating.engine.'.$id));
            }

            return $delegatingEngine;
        });

        $serviceManager->setAlias('templating', 'templating.engine.delegating');
    }

    /**
     * {@inheritDoc}
     */
    public function getConfigurationDefaults()
    {
        return array('framework' => array(
            'view'  => array(
                'engines' => array('php')
            )
        ));
    }

    /**
     * {@inheritDoc}
     */
    protected function processConfiguration(array $config, ServiceManager $serviceManager = null)
    {
        $config = $config['framework'];
        if (!isset($config['templating'])) {
            $config['templating'] = array();
        }

        if (isset($config['view'])) {
            $config['templating'] = $this->mergeConfiguration($config['view'], $config['templating']);
        }

        return $config['templating'];
    }
}
