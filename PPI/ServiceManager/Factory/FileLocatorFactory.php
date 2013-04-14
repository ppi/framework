<?php
/**
 * This file is part of the PPI Framework.
 *
 * @copyright   Copyright (c) 2011-2013 Paul Dragoonis <paul@ppi.io>
 * @license     http://opensource.org/licenses/mit-license.php MIT
 * @link        http://www.ppi.io
 */

namespace PPI\ServiceManager\Factory;

use PPI\View\FileLocator;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * FileLocator Factory.
 *
 * @author     Vítor Brandão <vitor@ppi.io> <vitor@noiselabs.org>
 * @package    PPI
 * @subpackage ServiceManager
 */
class FileLocatorFactory implements FactoryInterface
{
    /**
     * Create and return the datasource service.
     *
     * @param  ServiceLocatorInterface     $serviceLocator
     * @return \PPI\DataSource\DataSource;
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('Config');
        $appRootDir = $config['parameters']['app.root_dir'];
        $moduleManager = $serviceLocator->get('ModuleManager');
        $modulePaths = array();
        foreach ($moduleManager->getLoadedModules() as $module) {
            $modulePaths[] = $module->getPath();
        }

        return new FileLocator(array(
                'modules'     => $serviceLocator->get('ModuleManager')->getModules(),
                'modulesPath' => realpath($modulePaths[0]),
                'appPath'     => $appRootDir
            )
        );
    }
}