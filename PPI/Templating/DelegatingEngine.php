<?php
/**
 * This file is part of the PPI Framework.
 *
 * @category    PPI
 * @package     Templating
 * @copyright   Copyright (c) 2012 Paul Dragoonis <paul@ppi.io>
 * @license     http://opensource.org/licenses/mit-license.php MIT
 * @link        http://www.ppi.io
 */

namespace PPI\Templating;

use Symfony\Component\Templating\DelegatingEngine as BaseDelegatingEngine;

/**
 * DelegatingEngine selects an engine for a given template.
 *
 * @author Vítor Brandão <vitor@ppi.io>
 */
class DelegatingEngine extends BaseDelegatingEngine
{
}