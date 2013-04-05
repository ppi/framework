<?php
/**
 * This file is part of the PPI Framework.
 *
 * @copyright  Copyright (c) 2011-2013 Paul Dragoonis <paul@ppi.io>
 * @license    http://opensource.org/licenses/mit-license.php MIT
 * @link       http://www.ppi.io
 */

namespace PPI\Router;

/**
 * The routing helper for the controller.
 *
 * @package    PPI
 * @subpackage Router
 */
class RoutingHelper
{
    /**
     * The routing params
     *
     * @var array
     */
    protected $params = array();

    protected $activeRouteName;

    /**
     * Constructor.
     *
     * @param array $params
     *
     * @return void
     */
    public function __construct(array $params = array())
    {
        if (!empty($params)) {
            $this->setParams($params);
        }
    }

    /**
     * Obtain a param's value
     *
     * @param string $param The param name
     *
     * @return type
     *
     * @throws \InvalidArgumentException When the param does not exist
     */
    public function getParam($param)
    {
        if (!isset($this->params[$param])) {
            throw new \InvalidArgumentException('Unable to find routing param: ' . $param);
        }

        return $this->params[$param];
    }

    /**
     * Set a routing param's value
     *
     * @param string $param
     * @param string $value
     *
     * @return $this
     */
    public function setParam($param, $value)
    {
        $this->params[$param] = $value;

        return $this;
    }

    /**
     * Get all routing params
     *
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Set the routing params
     *
     * @param array $params
     *
     * @return $this
     */
    public function setParams(array $params)
    {
        $this->params = $params;

        return $this;
    }

    /**
     * Set the active route's name key
     *
     * @param  $name
     * @return $this
     */
    public function setActiveRouteName($name)
    {
        $this->activeRouteName = $name;

        return $this;
    }

    /**
     * Get the active route's name key
     *
     * @return mixed
     */
    public function getActiveRouteName()
    {
        return $this->activeRouteName;
    }

}
