<?php

/**
 * The default router class for the PPI Framework
 *
 * @author    Paul Dragoonis <dragoonis@php.net>
 * @license   http://opensource.org/licenses/mit-license.php MIT
 * @package   Core
 * @link      www.ppiframework.com
 *
 */
namespace PPI;

class Router implements Router\RouterInterface {

	/**
	 * An array of all the routes to be used
	 *
	 * @var null|array
	 */
	protected $_allRoutes = null;

	/**
	 * The matched route
	 *
	 * @var string
	 */
	protected $_matchedRoute = null;
	/**
	 * The file to get the routes from
	 *
	 * @var null|string
	 */
	protected $_routingFile = null;

	/**
	 * Check if we have ran the init() function before or not.
	 *
	 * @var bool
	 */
	protected $_ranInit = false;

	/**
	 * Check if we have found a match or not from our custom routes.
	 *
	 * @var bool
	 */
	protected $_foundMatch = false;

	/**
	 * The URI to be used for routing matches
	 *
	 * @var null|string
	 */
	protected $_uri = null;


	/**
	 * The constructor
	 */
	public function __construct(array $options = array()) {

		if (isset($options['routingFile'])) {
			$this->_routingFile = $options['routingFile'];
		}
	}

	public function match() {
		if(!$this->_ranInit) {
			$this->init();
		}
		return $this->_matchedRoute !== null;
	}

	/**
	 * Initialise the router and start grabbing routes
	 *
	 * @return void
	 */
	public function init() {

		include $this->getRoutingFile();

		// So all other functions have access to the routes.
		$this->_allRoutes = $routes;

		$uri = '/' . $this->_uri;
		$route = $uri;
		// Loop through the route array looking for wild-cards
		foreach ($routes as $key => $val) {
			// Convert wild-cards to RegEx
			$key = str_replace(array(':any', ':num'), array('.+', '[0-9]+'), $key);
			// Does the RegEx match?
			if (preg_match('#^' . $key . '$#', $uri)) {
				// Do we have a back-reference?
				if (false !== strpos($val, '$') && false !== strpos($key, '(')) {
					$val = preg_replace('#^' . $key . '$#', $val, $uri);
				}
				$this->_foundMatch = true;
				$route = $val;
				break;
			}
		}

		// If we are on the homepage, we need to match the __default__ route.
		if(!$this->_foundMatch && $this->_uri === '' && isset($routes['__default__'])) {
			$this->_foundMatch = true;
			$route = $routes['__default__'];
		}

		// We don't currently want to send query string data back up the chain, this can only effectively
		// Be matched currently using a route and our routes have matched nothing. Remove query string values.
		if( ($pos = strpos($route, '?')) !== false) {
			$route = substr($route, 0, $pos);
		}

		$this->setMatchedRoute($route);
	}

	/**
	 * Get the route currently matched
	 *
	 * @return string
	 */
	public function getMatchedRoute() {
		return $this->_matchedRoute;
	}

	/**
	 * Set the route currently matched
	 *
	 * @param string $route
	 * @return void
	 */
	public function setMatchedRoute($route) {
		$this->_matchedRoute = $route;
	}

	/**
	 * Get the routing file
	 *
	 * @return string
	 */
	public function getRoutingFile() {

		if ($this->_routingFile === null) {
			$this->_routingFile = APPFOLDER . 'Config/routes.php';
		}
		return $this->_routingFile;
	}

	/**
	 * Set the uri
	 *
	 * @param $uri
	 * @return void
	 */
	public function setUri($uri) {
		$this->_uri = $uri;
	}

	/**
	 * Get the uri
	 *
	 * @return
	 */
	public function getUri() {
		return $this->_uri;
	}

	public function getDefaultRoute() {
		return $this->_allRoutes['__default__'];
	}

	public function get404Route() {
		return $this->_allRoutes['__404__'];
	}
}
