<?php
namespace PPI\Test\Form;
use PPI\Form\Tag\Hidden,
    PPI\Form;
class HiddenTest extends \PHPUnit_Framework_TestCase {

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
        $output = $this->_form->hidden('hiddenName', array('value' => 'hiddenValue'))->render();
        $this->assertEquals($output, '<input type="hidden" name="hiddenName" value="hiddenValue">');
    }

    public function testCreateWithAttrs()
    {
        $output = $this->_form->hidden('hiddenName', array('value' => 'hiddenValue', 'id' => 'bar'))->render();
        $this->assertEquals($output, '<input type="hidden" name="hiddenName" value="hiddenValue" id="bar">');
    }

    public function testDirectClass()
    {
        $submit = new Hidden(array(
            'value' => 'hiddenValue',
            'name'  => 'hiddenName',
            'id'    => 'bar'
        ));
        $output = $submit->render();
        $this->assertEquals($output, '<input type="hidden" value="hiddenValue" name="hiddenName" id="bar">');
    }

    public function testDirectClass__toString()
    {
        $submit = new Hidden(array(
            'value' => 'hiddenValue',
            'name'  => 'hiddenName',
            'id'    => 'bar'
        ));
        $output = (string) $submit;
        $this->assertEquals($output, '<input type="hidden" value="hiddenValue" name="hiddenName" id="bar">');
    }

    public function testHasAttr()
    {
        $submit = new Hidden(array(
            'value' => 'Register',
            'name'  => 'foo',
            'id'    => 'bar'
        ));
        $this->assertTrue($submit->hasAttr('name'));
        $this->assertFalse($submit->hasAttr('nonexistantattr'));
    }

    public function testGetAttr()
    {
        $submit = new Hidden(array(
            'value' => 'Register',
            'name'  => 'foo',
            'id'    => 'bar'
        ));
        $this->assertEquals('Register', $submit->attr('value'));
    }

    public function testSetAttr()
    {
        $submit = new Hidden(array(
            'value' => 'Register',
            'name'  => 'foo',
            'id'    => 'bar'
        ));
        $submit->attr('foo', 'bar');
        $this->assertEquals('bar', $submit->attr('foo'));
    }

    public function testGetValues()
    {
        $hidden = new Hidden(array(
            'value' => 'hiddenvalue'
        ));
        $this->assertEquals('hiddenvalue', $hidden->getValue());
        $this->assertEquals('hiddenvalue', $hidden->attr('value'));
    }

    public function testSetValue()
    {
        $hidden = new Hidden();
        $hidden->setValue('hiddenvalue');
        $this->assertEquals('hiddenvalue', $hidden->getValue());
    }
}
