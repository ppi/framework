<?php
namespace PPI\Test\Form;
use PPI\Form\Tag\Radio;
class RadioboxTagTest extends \PHPUnit_Framework_TestCase {

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
        $output = $this->_form->radio('myradio')->render();
        $this->assertEquals($output, '<input type="radio" name="myradio">');
    }

    public function testCreateWithAttrs()
    {
        $output = $this->_form->radio('myradio', array('id' => 'bar'))->render();
        $this->assertEquals($output, '<input type="radio" name="myradio" id="bar">');
    }

    public function testDirectClass()
    {
        $radio =  new Radio(array(
            'value' => 'foo_radio',
            'name'  => 'myradio',
            'id'    => 'bar'
        ));
        $output = $radio->render();
        $this->assertEquals($output, '<input type="radio" value="foo_radio" name="myradio" id="bar">');
    }

    public function testDirectClass__toString()
    {
        $radio =  new Radio(array(
            'value' => 'foo_radio',
            'name'  => 'myradio',
            'id'    => 'bar'
        ));
        $output = (string) $radio;
        $this->assertEquals($output, '<input type="radio" value="foo_radio" name="myradio" id="bar">');
    }

    public function testHasAttr()
    {
        $radio =  new Radio(array(
            'value' => 'foo_radio',
            'name'  => 'myradio',
            'id'    => 'bar'
        ));
        $this->assertTrue($radio->hasAttr('name'));
        $this->assertFalse($radio->hasAttr('nonexistantattr'));
    }

    public function testGetAttr()
    {
        $radio =  new Radio(array(
            'value' => 'foo_radio',
            'name'  => 'myradio',
            'id'    => 'bar'
        ));
        $this->assertEquals('foo_radio', $radio->attr('value'));
    }

    public function testSetAttr()
    {
        $radio =  new Radio(array(
            'value' => 'foo_radio'
        ));
        $radio->attr('foo', 'bar');
        $this->assertEquals('bar', $radio->attr('foo'));
    }

    public function testGetValues()
    {
        $radio =  new Radio(array(
            'value' => 'foo_radio'
        ));
        $this->assertEquals('foo_radio', $radio->getValue());
        $this->assertEquals('foo_radio', $radio->attr('value'));
    }

    public function testSetValue()
    {
        $radio =  new Radio();
        $radio->setValue('foo_radio');
        $this->assertEquals('foo_radio', $radio->getValue());
    }
}
