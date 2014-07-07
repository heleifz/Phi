<?php

class CommandOptionTest extends PHPUnit_Framework_TestCase {

    public function testPositional() {
        $option = new \Phi\CommandOption;
        $this->assertTrue($option->isPositional());
        $option = new \Phi\CommandOption('hello');
        $this->assertFalse($option->isPositional());
    }

    public function testGetsSets() {
        $option = new \Phi\CommandOption('hello');
        $this->assertEquals('hello', $option->getName());
        $this->assertFalse($option->hasDefault());
        $option->setDefault(3);
        $this->assertTrue($option->hasDefault());
        $this->assertEquals(3, $option->getDefault());
    }

    public function testAlias() {
        $option = new \Phi\CommandOption('hello');
        $this->assertEquals(array(), $option->getAliases());
        $option->setAliases(array('h', 'hh', 'hh'));
        $this->assertEquals(array('h', 'hh'), $option->getAliases());
    }

    public function testValidator() {
        $option = new \Phi\CommandOption('hello');
        $this->assertTrue(call_user_func($option->getValidator()[0], 3));
        $this->assertTrue(call_user_func($option->getValidator()[0], 'hello'));
        $this->assertTrue(call_user_func($option->getValidator()[0], 'world'));
        $option->setValidator(function ($p) { return false; }, 'fail');
        $this->assertFalse(call_user_func($option->getValidator()[0], 3));
        $this->assertFalse(call_user_func($option->getValidator()[0], 'hello'));
        $this->assertFalse(call_user_func($option->getValidator()[0], 'world'));
    }

}


