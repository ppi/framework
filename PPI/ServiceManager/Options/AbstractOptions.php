<?php
/**
 * This file is part of the PPI Framework.
 *
 * @category    PPI
 * @package     ServiceManager
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
 * @author Vítor Brandão <vitor@ppi.io>
 */
abstract class AbstractOptions extends ParameterBag implements OptionsInterface
{
}
