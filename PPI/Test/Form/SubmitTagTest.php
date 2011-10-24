<?php
namespace PPI\Test\Form;
use PPI\Form\Tag\Submit;
class SubmitTest extends \PHPUnit_Framework_TestCase {

	function setUp() {
		$this->_form = new \PPI\Form();
	}

	function tearDown() {
		unset($this->_form);
	}

	function testCreate() {
		$output = $this->_form->submit('Register')->render();
		$this->assertEquals($output, '<input type="submit" value="Register">');
	}

	function testCreateWithAttrs() {
		$output = $this->_form->submit('Register', array('name' => 'foo', 'id' => 'bar'))->render();
		$this->assertEquals($output, '<input type="submit" value="Register" name="foo" id="bar">');
	}

	function testDirectClass() {
		$submit = new Submit(array(
			'value' => 'Register',
			'name'  => 'foo',
			'id'    => 'bar'
		));
		$output = $submit->render();
		$this->assertEquals($output, '<input type="submit" value="Register" name="foo" id="bar">');
	}


	function testDirectClass__toString() {
		$submit = new Submit(array(
			'value' => 'Register',
			'name'  => 'foo',
			'id'    => 'bar'
		));
		$output = (string) $submit;
		$this->assertEquals($output, '<input type="submit" value="Register" name="foo" id="bar">');
	}

	function testHasAttr() {
		$submit = new Submit(array(
			'value' => 'Register',
			'name'  => 'foo',
			'id'    => 'bar'
		));
		$this->assertTrue($submit->hasAttr('name'));
		$this->assertFalse($submit->hasAttr('nonexistantattr'));
	}

	function testGetAttr() {
		$submit = new Submit(array(
			'value' => 'Register',
			'name'  => 'foo',
			'id'    => 'bar'
		));
		$this->assertEquals('Register', $submit->attr('value'));
	}

	function testSetAttr() {
		$submit = new Submit(array(
			'value' => 'Register',
			'name'  => 'foo',
			'id'    => 'bar'
		));
		$submit->attr('foo', 'bar');
		$this->assertEquals('bar', $submit->attr('foo'));
	}

	function testGetValues() {
		$submit = new Submit(array(
			'value' => 'submitvalue'
		));
		$this->assertEquals('submitvalue', $submit->getValue());
		$this->assertEquals('submitvalue', $submit->attr('value'));
	}

	function testSetValue() {
		$submit = new Submit();
		$submit->setValue('submitvalue');
		$this->assertEquals('submitvalue', $submit->getValue());
	}
/*
	function testGetSetRule() {

		$field = new Submit();

		$field->setRule('required');
		$this->assertTrue(count($field->getRule('required')) > 0);

		$field->setRule('maxlength', 32);
		$rule = $field->getRule('maxlength');
		$this->assertEquals($rule['value'], 32);
		$this->assertEquals($rule['type'], 'maxlength');
	}
*/
}