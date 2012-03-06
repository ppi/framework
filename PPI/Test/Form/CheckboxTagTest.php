<?php
namespace PPI\Test\Form;
use PPI\Form;
use PPI\Form\Tag\Checkbox;
class CheckboxTagTest extends \PHPUnit_Framework_TestCase {

	function setUp() {
		$this->_form = new Form();
	}

	function tearDown() {
		unset($this->_form);
	}

	function testCreate() {
		$output = $this->_form->checkbox('mycheck')->render();
		$this->assertEquals($output, '<input type="checkbox" name="mycheck">');
	}

	function testCreateWithAttrs() {
		$output = $this->_form->checkbox('mycheck', array('id' => 'bar'))->render();
		$this->assertEquals($output, '<input type="checkbox" name="mycheck" id="bar">');
	}

	function testDirectClass() {
		$checkbox =  new Checkbox(array(
			'value' => 'foo_check',
			'name'  => 'mycheck',
			'id'    => 'bar'
		));
		$output = $checkbox->render();
		$this->assertEquals($output, '<input type="checkbox" value="foo_check" name="mycheck" id="bar">');
	}

	function testDirectClass__toString() {
		$checkbox =  new Checkbox(array(
			'value' => 'foo_check',
			'name'  => 'mycheck',
			'id'    => 'bar'
		));
		$output = (string) $checkbox;
		$this->assertEquals($output, '<input type="checkbox" value="foo_check" name="mycheck" id="bar">');
	}

	function testHasAttr() {
		$checkbox =  new Checkbox(array(
			'value' => 'foo_check',
			'name'  => 'mycheck',
			'id'    => 'bar'
		));
		$this->assertTrue($checkbox->hasAttr('name'));
		$this->assertFalse($checkbox->hasAttr('nonexistantattr'));
	}

	function testGetAttr() {
		$checkbox =  new Checkbox(array(
			'value' => 'foo_check',
			'name'  => 'mycheck',
			'id'    => 'bar'
		));
		$this->assertEquals('foo_check', $checkbox->attr('value'));
	}

	function testSetAttr() {
		$checkbox =  new Checkbox(array(
			'value' => 'foo_check'
		));
		$checkbox->attr('foo', 'bar');
		$this->assertEquals('bar', $checkbox->attr('foo'));
	}

	function testGetValues() {
		$checkbox =  new Checkbox(array(
			'value' => 'foo_check'
		));
		$this->assertEquals('foo_check', $checkbox->getValue());
		$this->assertEquals('foo_check', $checkbox->attr('value'));
	}

	function testSetValue() {
		$checkbox =  new Checkbox();
		$checkbox->setValue('foo_check');
		$this->assertEquals('foo_check', $checkbox->getValue());
	}
/*
	function testGetSetRule() {

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