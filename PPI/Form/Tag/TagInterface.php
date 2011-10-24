<?php
/**
 *
 * @author    Paul Dragoonis <dragoonis@php.net>
 * @license   http://opensource.org/licenses/mit-license.php MIT
 * @package   Form
 * @link      www.ppiframework.com
 */
namespace PPI\Form\Tag;
interface TagInterface {

	/**
	 * Getter and setter for attributes
	 *
	 * @param string $name
	 * @param mixed $value
	 * @return void
	 */
	function attr($name, $value = '');

	/**
	 * Render the tag
	 *
	 * @return void
	 */
	function render();

}