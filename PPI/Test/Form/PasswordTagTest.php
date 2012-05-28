<?php
namespace PPI\Test\Form;
use PPI\Form\Tag\Password;
class PasswordTagTest extends \PHPUnit_Framework_TestCase {

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
        $output = $this->_form->password('mypass')->render();
        $this->assertEquals($output, '<input type="password" name="mypass">');
    }

    public function testCreateWithAttrs()
    {
        $output = $this->_form->password('mypass', array('id' => 'bar'))->render();
        $this->assertEquals($output, '<input type="password" name="mypass" id="bar">');
    }

    public function testDirectClass()
    {
        $pass = new Password(array(
            'value' => 'foo_pass',
            'name'  => 'mypass',
            'id'    => 'bar'
        ));
        $output = $pass->render();
        $this->assertEquals($output, '<input type="password" value="foo_pass" name="mypass" id="bar">');
    }

    public function testDirectClass__toString()
    {
        $pass = new Password(array(
            'value' => 'foo_pass',
            'name'  => 'mypass',
            'id'    => 'bar'
        ));
        $output = (string) $pass;
        $this->assertEquals($output, '<input type="password" value="foo_pass" name="mypass" id="bar">');
    }

    public function testHasAttr()
    {
        $pass = new Password(array(
            'value' => 'foo_pass',
            'name'  => 'mypass',
            'id'    => 'bar'
        ));
        $this->assertTrue($pass->hasAttr('name'));
        $this->assertFalse($pass->hasAttr('nonexistantattr'));
    }

    public function testGetAttr()
    {
        $pass = new Password(array(
            'value' => 'foo_pass',
            'name'  => 'mypass',
            'id'    => 'bar'
        ));
        $this->assertEquals('foo_pass', $pass->attr('value'));
    }

    public function testSetAttr()
    {
        $pass = new Password(array(
            'value' => 'foo_pass'
        ));
        $pass->attr('foo', 'bar');
        $this->assertEquals('bar', $pass->attr('foo'));
    }

    public function testGetValues()
    {
        $pass = new Password(array(
            'value' => 'password'
        ));
        $this->assertEquals('password', $pass->getValue());
        $this->assertEquals('password', $pass->attr('value'));
    }

    public function testSetValue()
    {
        $pass = new Password();
        $pass->setValue('password');
        $this->assertEquals('password', $pass->getValue());
    }
}
