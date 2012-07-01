<?php

namespace PPI\Module\Routing\Loader;

use Symfony\Component\Routing\Loader\YamlFileLoader as BaseYamlFileLoader,
    Symfony\Component\Routing\RouteCollection;

class YamlFileLoader extends BaseYamlFileLoader
{
    protected $_defaults = array();

    public function setDefaults($defaults)
    {
        $this->_defaults = $defaults;
    }

    protected function parseRoute(RouteCollection $collection, $name, $config, $file)
    {
        if (!empty($this->_defaults)) {
            $config['defaults'] = array_merge($config['defaults'], $this->_defaults);
        }

        parent::parseRoute($collection, $name, $config, $file);
    }

}
