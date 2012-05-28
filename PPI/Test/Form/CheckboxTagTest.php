<?php
namespace PPI\Test\Form;
use PPI\Form;
use PPI\Form\Tag\Checkbox;
class CheckboxTagTest extends \PHPUnit_Framework_TestCase {

    public function setUp()
    {
        $this->_form = new Form();
    }

    public function tearDown()
    {
        unset($this->_form);
    }

    public function testCreate()
    {
        $output = $this->_form->checkbox('mycheck')->render();
        $this->assertEquals($output, '<input type="checkbox" name="mycheck">');
    }

    public function testCreateWithAttrs()
    {
        $output = $this->_form->checkbox('mycheck', array('id' => 'bar'))->render();
        $this->assertEquals($output, '<input type="checkbox" name="mycheck" id="bar">');
    }

    public function testDirectClass()
    {
        $checkbox =  new Checkbox(array(
            'value' => 'foo_check',
            'name'  => 'mycheck',
            'id'    => 'bar'
        ));
        $output = $checkbox->render();
        $this->assertEquals($output, '<input type="checkbox" value="foo_check" name="mycheck" id="bar">');
    }

    public function testDirectClass__toString()
    {
        $checkbox =  new Checkbox(array(
            'value' => 'foo_check',
            'name'  => 'mycheck',
            'id'    => 'bar'
        ));
        $output = (string) $checkbox;
        $this->assertEquals($output, '<input type="checkbox" value="foo_check" name="mycheck" id="bar">');
    }

    public function testHasAttr()
    {
        $checkbox =  new Checkbox(array(
            'value' => 'foo_check',
            'name'  => 'mycheck',
            'id'    => 'bar'
        ));
        $this->assertTrue($checkbox->hasAttr('name'));
        $this->assertFalse($checkbox->hasAttr('nonexistantattr'));
    }

    public function testGetAttr()
    {
        $checkbox =  new Checkbox(array(
            'value' => 'foo_check',
            'name'  => 'mycheck',
            'id'    => 'bar'
        ));
        $this->assertEquals('foo_check', $checkbox->attr('value'));
    }

    public function testSetAttr()
    {
        $checkbox =  new Checkbox(array(
            'value' => 'foo_check'
        ));
        $checkbox->attr('foo', 'bar');
        $this->assertEquals('bar', $checkbox->attr('foo'));
    }

    public function testGetValues()
    {
        $checkbox =  new Checkbox(array(
            'value' => 'foo_check'
        ));
        $this->assertEquals('foo_check', $checkbox->getValue());
        $this->assertEquals('foo_check', $checkbox->attr('value'));
    }

    public function testSetValue()
    {
        $checkbox =  new Checkbox();
        $checkbox->setValue('foo_check');
        $this->assertEquals('foo_check', $checkbox->getValue());
    }
/*
    public function testGetSetRule()
    {
        $field = new Checkbox();

        $field->setRule('required');
        $this->assertTrue(count($field->getRule('required')) > 0);

        $field->setRule('maxlength', 32);
        $rule = $field->getRule('maxlength');
        $this->assertEquals($rule['value'], 32);
        $this->assertEquals($rule['type'], 'maxlength');
    }
*/
}
