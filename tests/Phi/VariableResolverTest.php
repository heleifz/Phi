<?php

namespace Phi;

class VariableResolverTest extends \PHPUnit_Framework_TestCase {

    /**
     * @dataProvider resolveStringProvider
     */
    public function testResolveString($str, $context, $expected) {
        $this->assertEquals($expected,
            \Phi\VariableResolver::resolveString($str, $context));
    }

    /**
     * @dataProvider resolveArrayProvider
     */
    public function testResolveArray($arr, $context, $expected) {
        $this->assertEquals($expected,
            \Phi\VariableResolver::resolveArray($arr, $context));
    }

    /**
     * @dataProvider excludeKeyProvider
     */
    public function testExcludeKey($arr, $context, $excluded, $expected) {
        $this->assertEquals($expected,
            \Phi\VariableResolver::resolveArray($arr, $context, $excluded));
    }


    public function excludeKeyProvider() {
        return array(
            array(array(':hello'), array('hello' => 3), array(0), array(':hello')),
            array(array(':hello', ':world'), array('hello' => 3, 'world' => 'abc'), array(1), array('3', ':world')),
            array(array('a'=>':hello', 'b'=>':world'), array('hello' => 3, 'world' => 'abc'), array('a'), array('a' => ':hello', 'b'=> 'abc')),
            array(array('a'=>array(':hello'), 'b'=>array(':world')), array('hello' => 3, 'world' => 'abc'), array('a', 'b'), array('a' => array(':hello'), 'b' => array(':world'))),
        );
    }

    public function resolveArrayProvider() {
        return array(
            array(array(':hello'), array('hello' => 3), array('3')),
            array(array(':hello', ':world'), array('hello' => 3, 'world' => 'abc'), array('3', 'abc')),
            array(array('a'=>':hello', 'b'=>':world'), array('hello' => 3, 'world' => 'abc'), array('a' => 3, 'b'=> 'abc')),
            array(array('a'=>array(':hello'), 'b'=>array(':world')), array('hello' => 3, 'world' => 'abc'), array('a' => array(3), 'b'=> array('abc'))),
            array(array('a'=>array('hello'), 'b'=>array('world')), array('hello' => 3, 'world' => 'abc'), array('a' => array('hello'), 'b'=> array('world'))),
            array(array('a'=>array(array(array(':hello')))), array('hello' => 3), array('a' => array(array(array('3'))))),
        );
    }

    public function resolveStringProvider() {
        return array(
            array(':hello', array('hello' => 3), '3'),
            array(':hello:world', array('hello' => 3, 'world' => 'abc'), '3abc'),
            array('::hello:world', array('hello' => 3, 'world' => 'abc'), ':3abc'),
            array(':this:is:url', array('this' => '/', 'is' => 'foo', 'url' => '/bar'), '/foo/bar'),
            array(':foo:bar', array('bar' => 'abc'), ':fooabc'),
            array(':foo:bar', array(), ':foo:bar'),
        );
    }
}


