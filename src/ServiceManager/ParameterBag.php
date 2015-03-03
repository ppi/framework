<?php
/**
 * This file is part of the PPI Framework.
 *
 * @copyright   Copyright (c) 2012 Paul Dragoonis <paul@ppi.io>
 * @license     http://opensource.org/licenses/mit-license.php MIT
 * @link        http://www.ppi.io
 */

namespace PPI\Framework\ServiceManager;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag as BaseParameterBag;

/**
 * Holds parameters.
 *
 * An alternative implementation, instead of Symfony's ParameterBag, is to use
 * Zend\Stdlib\AbstractOptions or Symfony\Component\OptionsResolver\Options.
 *
 * @author     Vítor Brandão <vitor@ppi.io>
 * @package    PPI
 * @subpackage ServiceManager
 */
class ParameterBag extends BaseParameterBag implements \ArrayAccess, \IteratorAggregate, \Countable
{
    /**
     * @see \ArrayAccess::offsetExists()
     */
    public function offsetExists($option)
    {
        return $this->has($option);
    }

    /**
     * @see \ArrayAccess::offsetGet()
     */
    public function offsetGet($option)
    {
        return $this->get($option);
    }

    /**
     * @see \ArrayAccess::offsetSet()
     */
    public function offsetSet($option, $value)
    {
        $this->set($option, $value);
    }

    /**
     * @see \ArrayAccess::offsetUnset()
     */
    public function offsetUnset($option)
    {
        $this->remove($option);
    }

    /**
     * @see \Traversable::getIterator()
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->all());
    }

    /**
     * @see \Countable::count()
     */
    public function count()
    {
        return count($this->all());
    }

    /**
     * Replaces parameter placeholders (%name%) by their values in every string element of the $array.
     *
     * @param  array $data
     * @return array
     */
    public function resolveArray(array $data)
    {
        $self = $this;
        array_walk_recursive($data, function (&$value, $key) use ($self) {
            if (is_string($value)) {
                $value = $self->resolveString($value);
            }
        });

        return $data;
    }

    /**
     * Flattens an nested array of parameters
     *
     * The scheme used is:
     *   'key' => array('key2' => array('key3' => 'value'))
     * Becomes:
     *   'key.key2.key3' => 'value'
     *
     * This function takes an array by reference and will modify it
     *
     * @param array  &$parameters The array that will be flattened
     * @param array  $subnode     Current subnode being parsed, used internally for recursive calls
     * @param string $path        Current path being parsed, used internally for recursive calls
     */
    protected function flatten(array &$parameters, array $subnode = null, $path = null)
    {
        if (null === $subnode) {
            $subnode = & $parameters;
        }
        foreach ($subnode as $key => $value) {
            if (is_array($value)) {
                $nodePath = $path ? $path . '.' . $key : $key;
                $this->flatten($parameters, $value, $nodePath);
                if (null === $path) {
                    unset($parameters[$key]);
                }
            } elseif (null !== $path) {
                $parameters[$path . '.' . $key] = $value;
            }
        }
    }
}
