<?php
namespace PPI\Test\Form;
use PPI\Form\Rule\MaxLength;
class MaxLengthRuleTest extends \PHPUnit_Framework_TestCase {

	function setUp() {
		$this->_rule = new Maxlength();
	}

	function tearDown() {
		unset($this->_rule);
	}

    /**
     * @dataProvider providerForValidationTrue
     */
	function testValidatesTrue($size, $data) {
        $this->_rule->setRuleData($size);
        $this->assertTrue($this->_rule->validate($data));
    }

    /**
     * @dataProvider providerForValidationFalse
     */
	function testValidatesFalse($size, $data) {
        $this->_rule->setRuleData($size);
        $this->assertFalse($this->_rule->validate($data));
    }

    function providerForValidationTrue() {
        return array(
            array(10, '0123456789'),
            array(10, '   0123456789   '),//whitespace is ignored
            array(10, '0'),
            array(10, ''),
        );
    }

    function providerForValidationFalse() {
        return array(
            array(10, '0123456789012'),
            array(10, '  0123456789012  '),
        );
    }
}