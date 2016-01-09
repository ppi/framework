<?php
/**
 * This file is part of the PPI Framework.
 *
 * @copyright  Copyright (c) 2012 Paul Dragoonis <paul@ppi.io>
 * @license    http://opensource.org/licenses/mit-license.php MIT
 *
 * @link       http://www.ppi.io
 */

namespace PPI\Framework\Module\Listener;

use Zend\ModuleManager\Listener\ListenerOptions as BaseListenerOptions;

/**
 * ListenerOptions class.
 *
 * @todo Add inline documentation.
 *
 * @author     Paul Dragoonis <paul@ppi.io>
 */
class ListenerOptions extends BaseListenerOptions
{
    /**
     * @todo Add inline documentation.
     *
     * @var type
     */
    protected $routingEnabled;

    /**
     * @todo Add inline documentation.
     *
     * @param type $enabled
     */
    public function setRoutingEnabled($enabled)
    {
        $this->routingEnabled = $enabled;
    }

    /**
     * @todo Add inline documentation.
     *
     * @return bool
     */
    public function getRoutingEnabled()
    {
        return $this->routingEnabled;
    }
}
