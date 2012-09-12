<?php
/**
 * This file is part of the PPI Framework.
 *
 * @copyright  Copyright (c) 2012 Paul Dragoonis <paul@ppi.io>
 * @license    http://opensource.org/licenses/mit-license.php MIT
 * @link       http://www.ppi.io
 */
namespace PPI\Module\Routing;

/**
 * The routing helper for the controller.
 *
 * @package    PPI
 * @subpackage Module
 */
class RoutingHelper
{
    /**
     * The routing params
     *
     * @var array
     */
    protected $_params = array();

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
     *
     * @return void
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
     *
     * @return void
     */
    public function setParams(array $params)
    {
        $this->_params = $params;
    }

}
