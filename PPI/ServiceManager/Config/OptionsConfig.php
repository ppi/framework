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

use Zend\ServiceManager\Config;
use Zend\ServiceManager\ServiceManager;

/**
 * Registers App options in the ServiceManager.
 *
 * @author Vítor Brandão <vitor@ppi.io>
 */
class OptionsConfig extends Config
{
    protected $appConfig;

    public function __construct($appConfig, $config = array())
    {
        $this->appConfig = $appConfig;

        parent::__construct($config);
    }

    public function configureServiceManager(ServiceManager $serviceManager)
    {
        $serviceManager->setService('options', $this->appConfig);
    }
}
