<?php
namespace PPI\Test\Form;
use PPI\Form\Rule\Maxlength;
class MaxlengthRuleTest extends \PHPUnit_Framework_TestCase {

    public function setUp()
    {
        $this->_rule = new Maxlength();
    }

    public function tearDown()
    {
        unset($this->_rule);
    }

    /**
     * @dataProvider providerForValidationTrue
     */
    public function testValidatesTrue($size, $data)
    {
        $this->_rule->setRuleData($size);
        $this->assertTrue($this->_rule->validate($data));
    }

    /**
     * @dataProvider providerForValidationFalse
     */
    public function testValidatesFalse($size, $data)
    {
        $this->_rule->setRuleData($size);
        $this->assertFalse($this->_rule->validate($data));
    }

    public function providerForValidationTrue()
    {
        return array(
            array(10, '0123456789'),
            array(10, '   0123456789   '),//whitespace is ignored
            array(10, '0'),
            array(10, ''),
        );
    }

    public function providerForValidationFalse()
    {
        return array(
            array(10, '0123456789012'),
            array(10, '  0123456789012  '),
        );
    }
}
