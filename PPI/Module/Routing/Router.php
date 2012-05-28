<?php

/**
 * The PPI Template Helper for Assetic 
 *
 * @author    Paul Dragoonis <dragoonis@php.net>
 * @license   http://opensource.org/licenses/mit-license.php MIT
 * @link      http://www.ppi.io
 */

namespace PPI\Module\Routing;

use Symfony\Component\Routing\Router as BaseRouter,
	Symfony\Component\Routing\RequestContext;

/**
 * The PPI router
 *
 * @author Paul Dragoonis (dragoonis@php.net)
 */
class Router extends BaseRouter {
	
	public function __construct(RequestContext $requestContext, $collection, array $options = array()) {

		parent::setOptions($options);

		$this->collection = $collection;
		$this->context = $requestContext;

	}
	
	/**
	 * Set the route collection
	 * 
	 * @param $collection
	 */
	public function setRouteCollection($collection) {
		$this->collection = $collection;
	}
	
	/**
	 * Has the cache matcher class been generated
	 * 
	 * @return bool
	 */
	public function isMatcherCached() {
		return file_exists($this->options['cache_dir'].'/'.$this->options['matcher_cache_class'].'.php');
	}
	
	/**
	 * Has the cache url generator class been generated
	 * 
	 * @return bool
	 */
	public function isGeneratorCached() {
		return file_exists($this->options['cache_dir'].'/'.$this->options['generator_cache_class'].'.php');
	}
	
	/**
	 * Warm up the matcher and generator parts of the router
	 */
	public function warmUp() {
		
		$this->getMatcher();
		$this->getGenerator();
		
	}
	
}