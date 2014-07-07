<?php

class UtilsTest extends PHPUnit_Framework_TestCase {

    /**
     * @dataProvider urlDataProvider
     */
    public function testNormalizeUrl($raw, $normalized) {
        $this->assertEquals($normalized, \Phi\Utils::normalizeUrl($raw));
    }

    /**
     * @dataProvider insertVariablesProvider
     */
    public function testInsertVariables($template, $context, $expected) {
        $this->assertEquals($expected,
            \Phi\Utils::insertVariables($template, $context));
    }

    /**
     * @dataProvider mergeArrayProvider
     */
    public function testArrayMergeRecursiveDistinct($arr1, $arr2, $expected) {
        $this->assertEquals($expected,
            \Phi\Utils::arrayMergeRecursiveDistinct($arr1, $arr2));
    }

    /**
     * @dataProvider camelProvider
     */
    public function testCamelToLower($camel, $expected) {
        $this->assertEquals($expected, \Phi\Utils::camelToLower($camel));
    }

    public function camelProvider() {
        return array(
            array('helloWorld', 'hello-world'),
            array('hello', 'hello'),
            array('', ''),
            array('NiHaoMa', 'ni-hao-ma'),
            array('ABC', 'abc'),
        );
    }

    public function mergeArrayProvider() {
        return array(
            array(array(1, 2, 3), array(4, 5, 6), array(4, 5, 6, 1, 2, 3)),
            array(array(), array(4, 5, 6), array(4, 5, 6)),
            array(array('a' => 'b'), array('c' => 'd'), array('a' => 'b', 'c' => 'd')),
            array(array('a' => 'b'), array('a' => 'd'), array('a' => 'd')),
            array(array('a' => array(1, 2, 3)),
                array('a' => array(4, 5)), array('a' => array(4, 5, 1, 2, 3))),
            array(array('a' => array(1, 2, 3)),
                array('a' => 4), array('a' => 4)),
            array(array('a' => 4), array('a' => array(1, 2, 3)),
                array('a' => array(1, 2, 3))),
        );
    }

    public function insertVariablesProvider() {
        return array(
            array(':hello', array('hello' => 3), '3'),
            array(':hello:world', array('hello' => 3, 'world' => 'abc'), '3abc'),
            array('::hello:world', array('hello' => 3, 'world' => 'abc'), ':3abc'),
            array(':this:is:url', array('this' => '/', 'is' => 'foo', 'url' => '/bar'), '/foo/bar'),
            array(':foo:bar', array('bar' => 'abc'), ':fooabc'),
            array(':foo:bar', array(), ':foo:bar'),
        );
    }

    public function urlDataProvider() {
        return array(
            array('/hello/', '/hello/index.html'),
            array('hello.html', '/hello.html'),
            array('\\hello.css', '/hello.css'),
            array('foo/bar', '/foo/bar/index.html'),
            array('foo\\bar', '/foo/bar/index.html'),
            array('', '/index.html'),
        );
    }

}


