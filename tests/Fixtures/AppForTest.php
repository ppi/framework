<?php
/**
 * This file is part of the PPI Framework.
 *
 * @copyright  Copyright (c) 2011-2015 Paul Dragoonis <paul@ppi.io>
 * @license    http://opensource.org/licenses/mit-license.php MIT
 * @link       http://www.ppi.io
 */
namespace PPI\Framework\Tests\Fixtures;

use PPI\Framework\App;

/**
 * Class AppForTest
 * @package PPI\Framework\Fixtures
 *
 * @author Vítor Brandão <vitor@ppi.io>
 */
class AppForTest extends App
{
    public function isBooted()
    {
        return $this->booted;
    }
}
