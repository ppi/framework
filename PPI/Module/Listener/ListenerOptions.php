<?php

namespace PPI\Module\Listener;

use Zend\ModuleManager\Listener\ListenerOptions as BaseListenerOptions;

class ListenerOptions extends BaseListenerOptions
{
    protected $routingEnabled;

    public function setRoutingEnabled($enabled)
    {
        $this->routingEnabled = $enabled;
    }

    public function getRoutingEnabled()
    {
        return $this->routingEnabled;
    }

}
