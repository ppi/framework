<?php
/**
 * This file is part of the PPI Framework.
 *
 * @copyright  Copyright (c) 2012 Paul Dragoonis <paul@ppi.io>
 * @license    http://opensource.org/licenses/mit-license.php MIT
 *
 * @link       http://www.ppi.io
 */

namespace PPI\Framework\Config\Loader;

use Symfony\Component\Config\Loader\Loader;

/**
 * ArrayLoader loads app configuration from a PHP array.
 *
 * @author     Vítor Brandão <vitor@ppi.io>
 */
class ArrayLoader extends Loader
{
    /**
     * Loads a resource.
     *
     * @param mixed  $resource The resource
     * @param string $type     The resource type
     */
    public function load($resource, $type = null)
    {
        return $resource;
    }

    /**
     * Returns true if this class supports the given resource.
     *
     * @param mixed  $resource A resource
     * @param string $type     The resource type
     *
     * @return Boolean true if this class supports the given resource, false otherwise
     */
    public function supports($resource, $type = null)
    {
        return is_array($resource);
    }
}
