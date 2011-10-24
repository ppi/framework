<?php
/**
 * The main router driver interface
 *
 * @package   Core
 * @author    Paul Dragoonis <dragoonis@php.net>
 * @copyright 2001-2010 Digiflex Development Team
 * @license   http://opensource.org/licenses/mit-license.php MIT
 * @link      www.ppiframework.com
*/
namespace PPI\Router;
interface RouterInterface {

    /**
     * Just run the init() of the driver to embed this routing driver in the page life-cycle
     *
     * @abstract
     * @return void
     */
	function init();

}