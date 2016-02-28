<?php
namespace PPI\Framework\Module\Routing;

use Symfony\Component\Routing\RouteCollection;

trait SymfonyTrait {

    /**
     * Get the routes for this module
     *
     * @return RouteCollection
     */
    public function getRoutes()
    {
        return $this->loadSymfonyRoutes($this->getRoutingFilePath());
    }

    protected function getRoutingFilePath()
    {
        return $this->getPath() . '/resources/routes/symfony.yml';
    }

}