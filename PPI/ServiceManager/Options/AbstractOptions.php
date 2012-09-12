<?php
/**
 * This file is part of the PPI Framework.
 *
 * @copyright   Copyright (c) 2012 Paul Dragoonis <paul@ppi.io>
 * @license     http://opensource.org/licenses/mit-license.php MIT
 * @link        http://www.ppi.io
 */
namespace PPI\ServiceManager\Options;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

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
abstract class AbstractOptions extends ParameterBag implements OptionsInterface, \ArrayAccess, \IteratorAggregate, \Countable
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

}
