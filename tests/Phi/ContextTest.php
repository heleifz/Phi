<?php

namespace Phi;

class ContextTest extends \PHPUnit_Framework_TestCase {

	private $dataPath;

	public function setUp() {
		$this->dataPath = __DIR__.'/data/context';
	}

    public function testInitialize() {
    	$context = new \Phi\Context;
    	$this->assertEquals(array(), $context->toArray());
    	$context = new \Phi\Context(array('hello' => 'world'));
    	$this->assertEquals(array('hello' => 'world'), $context->toArray());
    }

    public function testGetSet() {
    	$context = new \Phi\Context;
    	$this->assertEquals(NULL, $context->get('foo.bar'));
    	$context->set('foo.bar', 3);
    	$this->assertEquals(3, $context->get('foo.bar'));
    	// test empty query
    	$this->assertEquals(array('foo' => array('bar' => 3)), $context->get(''));
    }

    public function testMultipleGet() {
    	$context = \Phi\Context::fromYML($this->dataPath.'/simple.yml');
    	$queries = array('', 'foo', 'foo.bar', 'foo.bar.hello');
    	$result = $context->get(array('', 'foo', 'foo.bar', 'foo.bar.hello'));
    	for ($i = 0; $i < count($queries); $i++) {
    		$this->assertEquals($context->get($queries[$i]), $result[$i]);
    	}
    }

    public function testFromYML() {
    	$context = \Phi\Context::fromYML($this->dataPath.'/simple.yml');
    	$this->assertEquals('world', $context->get('foo.bar.hello'));
    	$this->assertEquals(array('bar' => array('hello' => 'world')),
    		$context->get('foo'));
    }

    public function testMerge() {
    	$c1 = new \Phi\Context(array('hello' => 'world'));
    	$c2 = new \Phi\Context(array('hello' => 'nihao'));
    	$c1->merge($c2);
    	$this->assertEquals(array('hello' => 'nihao'), $c1->toArray());
    	$c3 = new \Phi\Context(array(1, 2, 3));
    	$c4 = new \Phi\Context(array(4, 5, 6));
    	$c3->merge($c4);
    	$this->assertEquals(array(4, 5, 6, 1, 2, 3), $c3->toArray());
    	$c3->merge($c2, 'foo');
    	$this->assertEquals(array(4, 5, 6, 1, 2, 3, 'foo' => array('hello' => 'nihao')), $c3->toArray());
    }
}


