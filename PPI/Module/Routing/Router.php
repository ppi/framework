<?php

namespace PPI\Module\Routing;

use Symfony\Component\Routing\Router as BaseRouter,
	Symfony\Component\Routing\RequestContext;

class Router extends BaseRouter {
	
	public function __construct(RequestContext $requestContext, $collection, array $options = array()) {

		$this->context = $requestContext;
		$this->collection = $collection;
		parent::setOptions($options);
		
	}
	
	public function setRouteCollection($collection) {
		$this->collection = $collection;
	}
	
}