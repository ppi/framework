<?php

/**
 * The Routing Helper For The Controller
 *
 * @package   Controller
 * @author    Paul Dragoonis <dragoonis@php.net>
 * @license   http://opensource.org/licenses/mit-license.php MIT
 * @link      http://www.ppi.io
 */

namespace PPI\Module\Routing;

class RoutingHelper
{
    /**
     * The routing params
     *
     * @var array
     */
    protected $_params = array();

    public function __construct(array $params = array())
    {
        if (!empty($params)) {
            $this->setParams($params);
        }

    }

    /**
     * Obtain a param's value
     *
     * @param  string                    $param The param name
     * @throws \InvalidArgumentException When the param does not exist
     */
    public function getParam($param)
    {
        if (!isset($this->_params[$param])) {
            throw new \InvalidArgumentException('Unable to find routing param: ' . $param);
        }

        return $this->_params[$param];

    }

    /**
     * Set a routing param's value
     *
     * @param string $param
     * @param string $value
     */
    public function setParam($param, $value)
    {
        $this->_params[$param] = $value;
    }

    /**
     * Get all routing params
     *
     * @return array
     */
    public function getParams()
    {
        return $this->_params;
    }

    /**
     * Set the routing params
     *
     * @param array $params
     */
    public function setParams(array $params)
    {
        $this->_params = $params;
    }

}
