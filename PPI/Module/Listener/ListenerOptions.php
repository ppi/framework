<?php
/**
 * This file is part of the PPI Framework.
 *
 * @copyright  Copyright (c) 2012 Paul Dragoonis <paul@ppi.io>
 * @license    http://opensource.org/licenses/mit-license.php MIT
 * @link       http://www.ppi.io
 */
namespace PPI\Module\Listener;

use Zend\ModuleManager\Listener\ListenerOptions as BaseListenerOptions;

/**
 * @todo Add inline documentation.
 *
 * @package    PPI
 * @subpackage Module
 */
class ListenerOptions extends BaseListenerOptions
{
    /**
     * @todo Add inline documentation.
     */
    protected $routingEnabled;

    /**
     * @todo Add inline documentation.
     *
     * @return void
     */
    public function setRoutingEnabled($enabled)
    {
        $this->routingEnabled = $enabled;
    }

    /**
     * @todo Add inline documentation.
     *
     * @return boolean
     */
    public function getRoutingEnabled()
    {
        return $this->routingEnabled;
    }

}
