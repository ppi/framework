<?php
/**
 * This file is part of the PPI Framework.
 *
 * @copyright  Copyright (c) 2012 Paul Dragoonis <paul@ppi.io>
 * @license    http://opensource.org/licenses/mit-license.php MIT
 * @link       http://www.ppi.io
 */
namespace PPI\Exception;

/**
 * @todo Add inline documentation.
 *
 * @package    PPI
 * @subpackage Exception
 */
interface HandlerInterface
{
    /**
     * Handle an exception.
     *
     * @param \Exception $e Exception object.
     *
     * @return void
     */
    public function handle(\Exception $e);
}
