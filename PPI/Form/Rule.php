<?php
/**
 * @author    Paul Dragoonis <dragoonis@php.net>
 * @license   http://opensource.org/licenses/mit-license.php MIT
 * @package   Form
 * @link      www.ppiframework.com
 */
namespace PPI\Form;
abstract class Rule {

	/**
	 * The abritary param
	 *
	 * @var array
	 */
	protected $_ruleData = null;

	/**
	 * The message for this rule
	 *
	 * @var null
	 */
	protected $_ruleMessage = null;

    /**
     * Validates data by this rule
     *
     * @param mixed $data
     * @return boolean
     */
    abstract public function validate($data);

    /**
     * The Constructor
     *
     * @param mixed $param
     */
    public function __construct($ruleData = null) {

		if($ruleData !== null) {
	        $this->setParam($ruleData);
		}
    }

    /**
     * Get the rule param
     *
     * @return string
     */
    public function getRuleData() {
        return $this->_ruleData;
    }

    /**
     * Sets the rules value
     *
     * @param mixed $value
     * @return void
     */
    public function setRuleData($value) {
        $this->_ruleData = $value;
    }

	/**
	 * Set the rule message
	 *
	 * @param string $message
	 * @return void
	 */
	public function setRuleMessage($message) {
		$this->_ruleMessage = $message;
	}

	/**
	 * Get the rule message
	 *
	 * @return string|null
	 */
	public function getRuleMessage() {
		return $this->_ruleMessage;
	}

}
