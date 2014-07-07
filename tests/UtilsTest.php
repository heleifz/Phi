<?php

class UtilsTest extends PHPUnit_Framework_TestCase {

    /**
     * @dataProvider urlDataProvider
     */
    public function testNormalizeUrl($raw, $normalized) {
        $this->assertEquals($normalized, \Phi\Utils::normalizeUrl($raw));
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


