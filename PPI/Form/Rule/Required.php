<?php
/**
 *
 * @author    Paul Dragoonis <dragoonis@php.net>
 * @license   http://opensource.org/licenses/mit-license.php MIT
 * @package   Form
 * @link      www.ppiframework.com
 */
namespace PPI\Form\Rule;
use PPI\Form\Rule;
class Required extends Rule {

	/**
	 * Validate against the Required rule
	 *
	 * @param string $data
	 * @return bool
	 */
    public function validate($data) {
        return trim($data) !== '';
    }

}
