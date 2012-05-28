<?php
namespace PPI\Test\Form;
use PPI\Form\Tag\Textarea;
class TextareaTagTest extends \PHPUnit_Framework_TestCase {

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
        $output = $this->_form->textarea('desc')->render();
        $this->assertEquals($output, '<textarea name="desc"></textarea>');
    }

    public function testCreateWithAttrs()
    {
        $output = $this->_form->textarea('desc', array('id' => 'bar'))->render();
        $this->assertEquals($output, '<textarea name="desc" id="bar"></textarea>');
    }

    public function testDirectClass()
    {
        $text = new Textarea(array(
            'value' => 'my description',
            'name'  => 'desc',
            'id'    => 'bar'
        ));
        $output = $text->render();
        $this->assertEquals($output, '<textarea name="desc" id="bar">my description</textarea>');
    }

    public function testDirectClass__toString()
    {
        $text = new Textarea(array(
            'value' => 'my description',
            'name'  => 'desc',
            'id'    => 'bar'
        ));
        $output = (string) $text;
        $this->assertEquals($output, '<textarea name="desc" id="bar">my description</textarea>');
    }

    public function testHasAttr()
    {
        $text = new Textarea(array(
            'value' => 'my description',
            'name'  => 'desc',
            'id'    => 'bar'
        ));
        $this->assertTrue($text->hasAttr('name'));
        $this->assertFalse($text->hasAttr('nonexistantattr'));
    }

    public function testGetAttr()
    {
        $text = new Textarea(array(
            'value' => 'my description',
            'name'  => 'desc',
            'id'    => 'bar'
        ));
        $this->assertEquals('desc', $text->attr('name'));
    }

    public function testSetAttr()
    {
        $text = new Textarea(array(
            'value' => 'my description'
        ));
        $text->attr('foo', 'bar');
        $this->assertEquals('bar', $text->attr('foo'));
    }

    public function testGetValues()
    {
        $text = new Textarea(array(
            'value' => 'textvalue'
        ));
        $this->assertEquals('textvalue', $text->getValue());
    }

    public function testSetValue()
    {
        $text = new Textarea();
        $text->setValue('my description');
        $this->assertEquals('my description', $text->getValue());
    }
}
