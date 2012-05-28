<?php
namespace PPI\Test\Form;
use PPI\Form\Tag\Text;
class TextTagTest extends \PHPUnit_Framework_TestCase {

    public function setUp()
    {
        $this->_form = new \PPI\Form();
    }

    public function tearDown()
    {
        unset($this->_form);
    }

    public function testCreate()
    {
        $output = $this->_form->text('username')->render();
        $this->assertEquals($output, '<input type="text" name="username">');
    }

    public function testCreateWithAttrs()
    {
        $output = $this->_form->text('username', array('id' => 'bar'))->render();
        $this->assertEquals($output, '<input type="text" name="username" id="bar">');
    }

    public function testDirectClass()
    {
        $text = new Text(array(
            'value' => 'Register',
            'name'  => 'username',
            'id'    => 'bar'
        ));
        $output = $text->render();
        $this->assertEquals($output, '<input type="text" value="Register" name="username" id="bar">');
    }

    public function testDirectClass__toString()
    {
        $text = new Text(array(
            'value' => 'Register',
            'name'  => 'username',
            'id'    => 'bar'
        ));
        $output = (string) $text;
        $this->assertEquals($output, '<input type="text" value="Register" name="username" id="bar">');
    }

    public function testHasAttr()
    {
        $text = new Text(array(
            'value' => 'Register',
            'name'  => 'username',
            'id'    => 'bar'
        ));
        $this->assertTrue($text->hasAttr('name'));
        $this->assertFalse($text->hasAttr('nonexistantattr'));
    }

    public function testGetAttr()
    {
        $text = new Text(array(
            'value' => 'Register',
            'name'  => 'username',
            'id'    => 'bar'
        ));
        $this->assertEquals('Register', $text->attr('value'));
    }

    public function testSetAttr()
    {
        $text = new Text(array(
            'value' => 'Register'
        ));
        $text->attr('foo', 'bar');
        $this->assertEquals('bar', $text->attr('foo'));
    }

    public function testGetValues()
    {
        $text = new Text(array(
            'value' => 'textvalue'
        ));
        $this->assertEquals('textvalue', $text->getValue());
        $this->assertEquals('textvalue', $text->attr('value'));
    }

    public function testSetValue()
    {
        $text = new Text();
        $text->setValue('textvalue');
        $this->assertEquals('textvalue', $text->getValue());
    }
/*
    public function testGetSetRule()
    {
        $field = new Text();

        $field->setRule('This field is required', 'required');
        $this->assertTrue();

        $field->setRule('maxlength', 32);
        $rule = $field->getRule('maxlength');
        $this->assertEquals($rule['value'], 32);
        $this->assertEquals($rule['type'], 'maxlength');
    }
*/
}
