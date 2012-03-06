<?php

/**
 *
 * @author    Paul Dragoonis <dragoonis@php.net>
 * @license   http://opensource.org/licenses/mit-license.php MIT
 * @package   Cache
 * @link	  http://www.ppiframework.com
 */
namespace PPI\Exception;
interface HandlerInterface {

	public function handle(\Exception $e);
}