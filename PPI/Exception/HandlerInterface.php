<?php

/**
 * This file is part of the PPI Framework.
 *
 * @category    PPI
 * @package     Core
 * @copyright   Copyright (c) 2012 Paul Dragoonis <paul@ppi.io>
 * @license     http://opensource.org/licenses/mit-license.php MIT
 * @link        http://www.ppi.io
 */

namespace PPI\Exception;

interface HandlerInterface
{
    public function handle(\Exception $e);
}
