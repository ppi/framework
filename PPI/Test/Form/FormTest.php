<?php
namespace PPI\Test\Form;
use PPI\Form\Tag\Form;
class HtmlTest extends \PHPUnit_Framework_TestCase {

    public function testCreateFormSetsActionAttribute()
    {
        $form = $this->createForm();
        $this->assertEquals('index.php', $form->attr('action'));
    }

    public function testCreatingFormWithNoParametersCreatesFormWithEmptyAction()
    {
        $form = new Form();
        $this->assertEquals('<form action="">', $form->render());
    }

    public function testCreateFormWithTwoParametersSetsMethod()
    {
        $form = $this->createFormWithMethod('post');
        $this->assertEquals('index.php', $form->attr('action'));
        $this->assertEquals('post', $form->attr('method'));
    }

    public function testRenderShouldRenderFormTagWithAttributes()
    {
        $form = $this->createForm();
        $this->assertEquals('<form action="index.php">', $form->render());
    }

    public function testToStringCallsRender()
    {
        $form = $this->createForm();
        $expected = '<form action="index.php">';
        $this->assertEquals($expected, $form->render());
        $this->assertEquals($expected, (string) $form);
    }

    public function testSetAttributesAreRendered()
    {
        $form = $this->createForm();
        $form->attr('name', 'myform');
        $expected = '<form action="index.php" name="myform">';
        $this->assertEquals($expected, $form->render());
    }

    public function testEmptyAttributesShouldBeRendered()
    {
        $form = $this->createForm();
        $form->attr('class', '');
        $expected = '<form action="index.php" class="">';
        $this->assertEquals($expected, $form->render());
    }

    public function testEmptyAttributesShouldBeRendered__toString()
    {
        $form = $this->createForm();
        $form->attr('class', '');
        $expected = '<form action="index.php" class="">';
        $this->assertEquals($expected, (string) $form);
    }

    public function testRenderedAttributesAreProperlyEscaped()
    {
        $form = $this->createForm('Some attributes may \'">< contain garbage');
        $expected = '<form action="Some attributes may &#039;&quot;&gt;&lt; contain garbage">';
        $this->assertEquals($expected, $form->render());

    }

    private function createFormWithMethod($method, $action = 'index.php')
    {
        return new Form(array('action' => $action, 'method' => $method));
    }

    private function createForm($action = 'index.php')
    {
        return new Form(array('action' => $action));
    }
}

