<?php
/**
 *
 * @author    Paul Dragoonis <dragoonis@php.net>
 * @license   http://opensource.org/licenses/mit-license.php MIT
 * @package   Form
 * @link      www.ppiframework.com
 */
namespace PPI\Form\Rule;

class Maxlength extends \PPI\Form\Rule {

	/**
	 * Validate our maxlength rule
	 *
	 * @param string $data
	 * @return bool
	 */
    public function validate($data) {
        return strlen(trim($data)) <= $this->getRuleData();
    }

}
