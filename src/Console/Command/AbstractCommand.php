<?php
/**
 * This file is part of the PPI Framework.
 *
 * @copyright  Copyright (c) 2011-2013 Paul Dragoonis <paul@ppi.io>
 * @license    http://opensource.org/licenses/mit-license.php MIT
 *
 * @link       http://www.ppi.io
 */

namespace PPI\Framework\Console\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

/**
 * Base class for all commands.
 *
 * @author      Vítor Brandão <vitor@ppi.io>
 */
abstract class AbstractCommand extends ContainerAwareCommand
{
    /**
     * @return \Zend\ServiceManager\ServiceManagerInterface
     */
    protected function getServiceManager()
    {
        return $this->getContainer();
    }
}
