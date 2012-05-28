<?php
namespace PPI\Test\Form;
use PPI\Form\Rule\Required;
class RequiredRuleTest extends \PHPUnit_Framework_TestCase {

    public function setUp()
    {
        $this->_rule = new Required();
    }

    public function tearDown()
    {
        unset($this->_rule);
    }

    /**
     * @dataProvider providerForValidationTrue
     */
    public function testValidatesTrue($data)
    {
        $this->assertTrue($this->_rule->validate($data));
    }

    /**
     * @dataProvider providerForValidationFalse
     */
    public function testValidatesFalse($data)
    {
        $this->assertFalse($this->_rule->validate($data));
    }

    public function providerForValidationTrue()
    {
        return array(
            array('foo'),
            array(1),
            array(0), //zero could be a valid form value
        );
    }

    public function providerForValidationFalse()
    {
        return array(
            array(''),
            array(' '), //whitespace is not valid
        );
    }
}
