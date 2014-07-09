<?php

namespace Phi;

use \Mockery as m;

class FrontMatterTest extends \PHPUnit_Framework_TestCase {

    /**
     * @dataProvider textProvider
     */
    public function testParse($text, $expected) {
        $frontMatter = new \Phi\FrontMatter;
        $this->assertEquals($expected, $frontMatter->parse($text));
    }

    public function textProvider() {
        return array(
            array('no metadata', array('metadata' => array(), 'content' => 'no metadata')),
            array('---wrong metadata', NULL),
            array('---', NULL),
            array('--', array('metadata' => array(), 'content' => '--')),
            array('', array('metadata' => array(), 'content' => '')),
            array("---\nhello:world---body", array('metadata' => array('hello'=>'world'), 'content' => 'body')),
            array("---\nhello:  world\n---\nbody", array('metadata' => array('hello'=>'world'), 'content' => "\nbody")),
            array("---\nhello:  world\n--\nbody", NULL),
        );
    }
}


